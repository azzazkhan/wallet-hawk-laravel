<?php

namespace App\Traits;

use App\Models\Wallet;
use CStr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

trait HandlesERC20Transactions
{
    // Write your code here

    private function fetchFromEtherscanAPI(
        string $wallet_id,
        string $limit,
        ?int $page = 1,
    ): array {
        if (!$this->incrementEtherscanCounter())
            throw new TooManyRequestsHttpException(
                2, // Retry after (seconds)
                'Server overloaded, please try again in few moments',
            );

        // Keep track of how many calls we have consumed so far
        $this->incrementEtherscanAPICallCounter();

        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get('https://api.etherscan.io/api', [
                'address' => $wallet_id,
                'page'    => $page,
                'offset'  => $limit,
                'apikey'  => config('hawk.etherscan.api_key')
            ]);

        $this->decrementEtherscanCounter();

        if ($response->serverError())
            throw new InternalErrorException('The records service is having issues!');

        $data = $response?->json();

        return is_array($data['result']) ? $data['result'] : [];
    }

    private function saveEtherscanEvents(): void {}
    private function saveRawEtherscanEvents(): void {}

    private function parseEtherscanEvent(): array
    {
        return [];
    }

    /**
     * ===================================
     * WALLET LOCKOUT TIMER MANAGEMENT
     * ===================================
     */
    /**
     * Checks if Etherscan rate limiter timer has expired for specified wallet
     * address or not.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function hasEtherscanCooledDown(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        // The wallet has never been searched before
        if (!$wallet) {
            Log::debug('Wallet document does not exist, this means its first request', [
                'cooled_down' => true
            ]);

            return true;
        }

        $cooled_down =  (int) $wallet->last_etherscan_request->format('U') + config('hawk.etherscan.limits.default') <=
            (int) now()->format('U');

        Log::debug('Checking if wallet has cooled down or not', [
            'cooled_down' => $cooled_down
        ]);

        return $cooled_down;
    }

    /**
     * Updates Etherscan rate limiter timer for specified wallet address.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function updateEtherscanLockoutTimer(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if (!$wallet) { // If walled records does not exists then create one
            Log::debug('Updating rate limiter timer, creating new record');

            Wallet::create([
                'wallet_id' => $wallet_id,
                'last_etherscan_request' => now()->format('Y-m-d H:i:s')
            ]);
        } else { // Update the timer on existing record
            Log::debug('Updating rate limiter timer');

            $wallet->update([
                'last_etherscan_request' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Checks if Etherscan pagination rate limiter timer has expired for
     * specified wallet address or not.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function hasEtherscanPaginationTimerExhausted(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();


        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        // Pagination timer value does not exist that means it's never
        // wallet transactions has never been paginated
        if (!$wallet->last_etherscan_pagination)
            return true;

        // Compare pagination time with current time
        $exhausted =  (int) $wallet->last_etherscan_pagination->format('U') + config('hawk.etherscan.limits.pagination') <=
            (int) now()->format('U');

        Log::debug('Checking if pagination timer has exhausted for wallet or not', [
            'exhausted' => $exhausted
        ]);

        return $exhausted;
    }


    /**
     * Updates rate image.png pagination limiter timer for specified wallet
     * address.
     *
     * @param string $wallet_id The wallet address
     *
     * @return void
     */
    private function updateEtherscanPaginationTimer(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        $wallet->update([
            'last_etherscan_pagination' => now()->format('Y-m-d H:i:s')
        ]);

        Log::debug('Updating etherscan pagination timer');
    }

    /**
     * Checks if all Etherscan transactions are fetched (indexed) from the
     * beginning for the specified wallet address or not.
     *
     * @param string $wallet_id Wallet address to be checked for
     *
     * @return bool
     */
    private function hasEtherscanIndexed(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        Log::debug('Checking if etherscan wallet is indexed or not', [
            'indexed' => $wallet?->etherscan_indexed ? true : false,
        ]);

        if (!$wallet) // Wallet does not exists!
            return false;

        return (bool) $wallet->etherscan_indexed;
    }

    /**
     * Marks the specified wallet as Etherscan indexed meaning all of
     * transactions for this wallet (from beginning) have been saved by us.
     *
     * @param string $wallet_id The wallet address
     *
     * @return void
     */
    private function setEtherscanIndexed(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        Log::debug('Setting wallet as etherscan indexed', [
            'exists'  => $wallet ? true : false
        ]);

        // If the wallet record does not exists then create a new one
        if (!$wallet)
            Wallet::create([
                'wallet_id'       => $wallet_id,
                'etherscan_index' => true,
            ]);

        else
            $wallet->update(['etherscan_indexed' => true]);
    }

    /**
     * ==============================
     * API CALLS COUNTER MANAGEMENT
     * ==============================
     */
    /**
     * Makes sure we can increment the Etherscan API calls counter then
     * increments the counter.
     *
     * @return bool
     */
    private function incrementEtherscanCounter(): bool
    {
        if (!$this->canIncrementEtherscan()) return false;
        Cache::increment('etherscan_counter');

        return true;
    }

    /**
     * Decrements the Etherscan API calls counter.
     *
     * @return void
     */
    private function decrementEtherscanCounter(): void
    {
        $counter = Cache::get('etherscan_counter', function () {
            Cache::put('etherscan_counter', 0);

            return 0;
        });

        // If somehow counter has gone below zero then set it back to zero
        if ($counter < 0)
            Cache::put('etherscan_counter', 0);

        // Decrement the counter if it could be
        if ($counter > 1)
            Cache::decrement('etherscan_counter');
    }

    /**
     * Checks if we can increment the Etherscan calls counter or not. This
     * counter's intended purpose is that we cannot exceed our specified max
     * calls/sec and violate Etherscan API TOS.
     *
     * @return bool
     */
    private function canIncrementEtherscan(): bool
    {
        $counter = Cache::get('etherscan_counter', function () {
            Cache::put('etherscan_counter', 0);

            return 0;
        });

        return $counter < config('hawk.etherscan.network.max_calls');
    }

    /**
     * Increments the total Etherscan API call counter used to cap daily number
     * of API calls sent.
     *
     * @return void
     */
    private function incrementEtherscanAPICallCounter(): void
    {
        if (Cache::has('etherscan_calls_count'))
            Cache::increment('etherscan_calls_count');
        else
            Cache::put('etherscan_calls_count', 1);
    }
}
