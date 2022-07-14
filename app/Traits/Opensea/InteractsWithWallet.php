<?php

namespace App\Traits\Opensea;

use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

trait InteractsWithWallet
{
    /**
     * Checks whether cache rate limit timer has expired or not.
     *
     * @param string $wallet
     *
     * @return bool
     */
    private function hasCooledDown(string $wallet): bool
    {
        Log::debug('Checking if caching timer has cooled down or not');

        $wallet = $this->getWallet($wallet);

        if (!$wallet) {
            Log::debug('Wallet record does not exists this means it is cooled down');
            return true;
        }

        $cooled_down = (int) $wallet->last_opensea_request->format('U') + config('hawk.opensea.limits.default') <=
            (int) now()->format('U');

        if ($cooled_down) {
            Log::debug('Wallet caching timer has exhausted');
            return true;
        }

        Log::debug('Wallet cache timer has not cooled down yet');
        return false;
    }

    /**
     * Updates cache timer for specified wallet address.
     *
     * @param string $wallet_id
     *
     * @return void
     */
    private function updateLockout(string $wallet_id): void
    {
        Log::debug('Updating cache timer for wallet');

        // Will create new wallet if not exists or return fetched record if
        // already exists
        $wallet = $this->createWallet($wallet_id);

        $wallet->update([
            'last_opensea_request' => now()->format('Y-m-d H:i:s')
        ]);

        Log::debug('Update cache timer for passed wallet');
    }

    /**
     * Checks if pagination cache timer has expired for specified wallet
     * address or not.
     *
     * @param string $wallet_id
     *
     * @return bool
     *
     * @throws \Symfony\Component\HttpFoundation\Exception\BadRequestException
     */
    private function hasPaginationCooledDown(string $wallet_id): bool
    {
        Log::debug('Checking if pagination timer has cooled down or not');

        $wallet = $this->getWallet($wallet_id);

        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        // Pagination timer value does not exist that means it's never
        // wallet transactions has never been paginated
        if (!$wallet->last_opensea_pagination)
            return true;

        $exhausted = (int) $wallet->last_opensea_pagination->format('U') + config('hawk.opensea.limits.pagination') <= (int) now()->format('U');

        if ($exhausted) {
            Log::debug('Pagination timer has cooled down');
            return true;
        }

        Log::debug('Pagination timer not has cooled down yet');
        return false;
    }

    /**
     * Updates Opensea pagination cache timer for specified wallet address.
     *
     * @param string $wallet_id
     *
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\Exception\BadRequestException
     */
    private function updatePaginationLockout(string $wallet_id): void
    {
        Log::debug('Updating pagination timer for wallet address');

        $wallet = $this->getWallet($wallet_id); // Get wallet record

        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        $wallet->update([
            'last_opensea_pagination' => now()->format('Y-m-d H:i:s')
        ]);

        Log::debug('Updated pagination timer for passed wallet address');
    }

    /**
     * Checks if record exists for specified wallet address or not.
     *
     * @param string $wallet_id
     *
     * @return bool
     */
    private function walletExists(string $wallet_id): bool
    {
        Log::debug('Checking if wallet record exists in database or not');

        $wallet =  $this->getWallet($wallet_id);

        if ($wallet) {
            Log::debug('Wallet record exists in database');
            return true;
        }

        Log::debug('Wallet record does not exists in database');
        return false;
    }

    /**
     * Creates and returns new wallet record for specified wallet address or
     * returns existing record if record for same wallet already exists.
     *
     * @param string $wallet_id
     *
     * @return \App\Models\Wallet
     */
    private function createWallet(string $wallet_id): Wallet
    {
        Log::debug('Creating new record for wallet');

        $wallet = $this->getWallet($wallet_id);

        if (!$wallet) {
            Log::debug('Wallet record does not exist, creating new one');
            return Wallet::create(['wallet_id' => $wallet]);
        }

        Log::debug('Wallet record already exist, returning existing record');

        return $wallet;
    }

    /**
     * Fetches and returns wallet record against specified wallet address or
     * null if the record does not exists.
     *
     * @param string $wallet_id
     *
     * @return \App\Models\Wallet
     */
    private function getWallet(string $wallet_id): Wallet|null
    {
        Log::debug('Fetching wallet record from database');

        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if ($wallet)
            Log::debug('Wallet record exists in database, returning it');
        else
            Log::debug('Wallet record does not exists, returning null');

        return $wallet;
    }
}
