<?php

namespace App\Traits;

use App\Models\Wallet;
use App\Models\Opensea;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

trait HandlesOpenseaTransactions
{
    /**
     * Fetches ERC721 and ERC1155 transactions for specified wallet address
     * from Opensea API and also applies specified filers.
     *
     * @param string $wallet_id Wallet address for which transactions need to
     *                          be fetched
     * @param ?string $type (Optional) Opensea event type
     * @param ?string $cursor (Optional) Opensea pagination cursor
     * @param ?int $before (Option) Event ID (for custom pagination)
     * @param ?int $after (Option) Event ID (for custom pagination)
     *
     * @return array
     */
    private function fetchFromOpenseaAPI(
        string $wallet_id,
        ?string $type = null,
        ?string $cursor = null,
        ?int $before = null,
        ?int $after = null,
    ): array {
        // Make sure we have not reach API calls limit before trying to
        // initiate new API request
        if (!$this->incrementOpenseaCounter())
            throw new TooManyRequestsHttpException(
                2, // Retry after (seconds)
                'Server overloaded, please try again in few moments',
            );

        // Keep track of how many calls we have consumed so far
        $this->incrementOpenseaAPICallCounter();

        $response = Http::retry(3, 300)
            ->acceptJson()
            ->withHeaders([
                "X-API-KEY" => config('hawk.opensea.api_key')
            ])
            ->get('https://api.opensea.io/api/v1/events', [
                'account_address' => $wallet_id,
                'event_type'      => in_array($type, config('hawk.opensea.event.types')) ? $type : null,
                'cursor'          => $cursor && preg_match('/(([A-z0-9])=?){60,}$/', $cursor) ? $cursor : null,
                'occurred_before' => $before,
                'occurred_after'  => $after,
            ]);

        $this->decrementOpenseaCounter();

        if ($response->serverError())
            throw new InternalErrorException('The records service is having issues!');

        return $response->json();
    }

    /**
     * ========================================
     * UTILITY FUNCTIONS (PARSING, SAVING...)
     * ========================================
     */
    /**
     * Saves passed parsed Opensea events and ignores existing events.
     *
     * @param Collection<array> $events Parsed Opensea events.
     *
     * @return array
     */
    private function saveOpenseaEvents(Collection $events): array
    {
        // Check if any of passed events already exist in database or not
        $existing_events = Opensea::whereIn(
            'event_id',
            $events
                ->map(fn ($event) => $event['event_id'])
                ->toArray()
        )->get();

        // If no record exists then save and return them
        if (!$existing_events || $existing_events->count() === 0)
            return [
                'uniques'  => count($events),
                'existing' => 0,
                'events'   => $events->map(fn ($event) => Opensea::create($event))
            ];

        // Grab event IDs from existing records (for filtering)
        $existing_ids = $existing_events
            ->map(fn ($event) => $event['event_id']);

        // Get only unique records that do not exist in database
        $uniques = $events->filter(fn ($event) => $existing_ids->contains($event['event_id']));

        // All records already exist in database, return those
        if (!$uniques || $uniques->empty()) return [
            'events'   => $existing_events,
            'existing' => $existing_events->count(),
            'uniques'  => 0,
        ];

        // Return all events
        return [
            'events'   => array_merge($existing_events, Opensea::create($uniques)),
            'existing' => $existing_events->count(),
            'uniques'  => 0,
        ];
    }

    /**
     * Parses raw Opensea events data using `parseOpenseaEvent` and saves them
     * in database using `saveOpenseaEvents` method.
     *
     * @param $events Array of Opensea events (fetched from `asset_events`)
     *
     * @return array
     */
    private function saveRawOpenseaEvents(array $events): array
    {
        // Parsed events into indexable form
        $events = collect($events)
            ->map(fn ($event) => $this->parseOpenseaEvent($event));

        return $this->saveOpenseaEvents($events);
    }

    /**
     * Parses passed raw Opensea event data (fetched form API) and converts it
     * into `\App\Models\Opensea` compatible schema.
     *
     * @param array $event Raw Opensea event data (element of `asset_events`)
     *
     * @return array
     */
    private function parseOpenseaEvent(array $event): array
    {
        $asset = $event['asset'];
        $contract = $event['asset']['asset_contract'];
        $payment_token = $event['payment_token'];

        return [
            'schema'          => strtoupper($contract['schema_name']),
            'event_type'      => strtolower($event['event_type']),
            'event_id'        => $event['id'],
            'event_timestamp' => (new Carbon($event['event_timestamp']))->format('Y-m-d H:i:s'),

            'media'           => [
                'image' => [
                    'url'       => $asset['image_url'] ?: null,
                    'original'  => $asset['image_original_url'] ?: null,
                    'preview'   => $asset['image_preview_url'] ?: null,
                    'thumbnail' => $asset['image_thumbnail_url'] ?: null,
                ],
                'animation' => [
                    'url'       => $asset['animation_url'] ?: null,
                    'original'  => $asset['animation_original_url'] ?: null,
                ],
            ],
            'asset'           => [
                'id'            => (int) $asset['id'],
                'name'          => $asset['name'],
                'description'   => $asset['description'] ?: null,
                'external_link' => $asset['external_link'] ?: null,
            ],
            'payment_token'   => $payment_token ? [
                'decimals' => (int) $payment_token['decimals'],
                'symbol'   => $payment_token['symbol'],
                'eth'      => (string) $payment_token['eth_price'],
                'usd'      => (string) $payment_token['usd_price'],
            ] : null,
            'contract'        => [
                'address' => $contract['address'],
                'type'    => $contract['asset_contract_type'],
                'date'    => (new Carbon($contract['created_date']))->format('Y-m-d H:i:s'),
            ],
            'accounts'        => [
                'from'   => $event['from_account'] ? ($event['from_account']['address'] ?: null) : null,
                'to'     => $event['to_account'] ? ($event['to_account']['address'] ?: null) : null,
                'seller' => $event['seller'] ? ($event['seller']['address'] ?: null) : null,
                'winner' => $event['winner_account'] ? ($event['winner_account']['address'] ?: null) : null,
            ],
        ];
    }

    /**
     * ===================================
     * WALLET LOCKOUT TIMER MANAGEMENT
     * ===================================
     */
    /**
     * Checks if Opensea rate limiter timer has expired for specified wallet
     * address or not.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function hasOpenseaCooledDown(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        // The wallet has never been searched before
        if (!$wallet) {
            Log::debug('Wallet document does not exist, this means its first request', [
                'cooled_down' => true
            ]);

            return true;
        }

        $cooled_down =  (int) $wallet->last_opensea_request->format('U') + config('hawk.opensea.limits.default') <=
            (int) now()->format('U');

        Log::debug('Checking if wallet has cooled down or not', [
            'cooled_down' => $cooled_down
        ]);

        return $cooled_down;
    }

    /**
     * Updates Opensea rate limiter timer for specified wallet address.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function updateOpenseaLockoutTimer(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if (!$wallet) { // If walled records does not exists then create one
            Log::debug('Updating rate limiter timer, creating new record');

            Wallet::create([
                'wallet_id' => $wallet_id,
                'last_opensea_request' => now()->format('Y-m-d H:i:s')
            ]);
        } else { // Update the timer on existing record
            Log::debug('Updating rate limiter timer');

            $wallet->update([
                'last_opensea_request' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Checks if Opensea pagination rate limiter timer has expired for
     * specified wallet address or not.
     *
     * @param string $wallet_id The wallet address
     *
     * @return bool
     */
    private function hasOpenseaPaginationTimerExhausted(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();


        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        // Pagination timer value does not exist that means it's never
        // wallet transactions has naver been paginated
        if (!$wallet->last_opensea_pagination)
            return true;

        // Compare pagination time with current time
        $exhausted =  (int) $wallet->last_opensea_pagination->format('U') + config('hawk.opensea.limits.pagination') <=
            (int) now()->format('U');

        Log::debug('Checking if pagination timer has exhausted for wallet or not', [
            'exhausted' => $exhausted
        ]);

        return $exhausted;
    }


    /**
     * Updates rate Opensea pagination limiter timer for specified wallet
     * address.
     *
     * @param string $wallet_id The wallet address
     *
     * @return void
     */
    private function updateOpenseaPaginationTimer(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if (!$wallet)
            throw new BadRequestException(
                'Requested wallet\'s record does not exist! Please go to first page.'
            );

        $wallet->update([
            'last_opensea_pagination' => now()->format('Y-m-d H:i:s')
        ]);

        Log::debug('Updating opensea pagination timer');
    }

    /**
     * Checks if all Opensea transactions are fetched (indexed) from the
     * beginning for the specified wallet address or not.
     *
     * @param string $wallet_id Wallet address to be checked for
     *
     * @return bool
     */
    private function hasOpenseaIndexed(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        Log::debug('Checking if opensea wallet is indexed or not', [
            'indexed' => $wallet?->opensea_indexed ? true : false,
        ]);

        if (!$wallet) // Wallet does not exists!
            return false;

        return (bool) $wallet->opensea_indexed;
    }

    /**
     * Marks the specified wallet as Opensea indexed meaning all of
     * transactions for this wallet (from beginning) have been saved by us.
     *
     * @param string $wallet_id The wallet address
     *
     * @return void
     */
    private function setOpenseaIndexed(string $wallet_id): void
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        Log::debug('Setting wallet as opensea indexed', [
            'exists'  => $wallet ? true : false
        ]);

        // If the wallet record does not exists then create a new one
        if (!$wallet)
            Wallet::create([
                'wallet_id'       => $wallet_id,
                'opensea_indexed' => true,
            ]);

        else
            $wallet->update(['opensea_indexed' => true]);
    }

    /**
     * ==============================
     * API CALLS COUNTER MANAGEMENT
     * ==============================
     */
    /**
     * Makes sure we can increment the Opensea API calls counter then increments
     * the counter.
     *
     * @return bool
     */
    private function incrementOpenseaCounter(): bool
    {
        if (!$this->canIncrementOpensea()) return false;
        Cache::increment('opensea_counter');

        return true;
    }

    /**
     * Decrements the opensea API calls counter.
     *
     * @return void
     */
    private function decrementOpenseaCounter(): void
    {
        $counter = Cache::get('opensea_counter', function () {
            Cache::put('opensea_counter', 0);

            return 0;
        });

        // If somehow counter has gone below zero then set it back to zero
        if ($counter < 0)
            Cache::put('opensea_counter', 0);

        // Decrement the counter if it could be
        if ($counter > 1)
            Cache::decrement('opensea_counter');
    }

    /**
     * Checks if we can increment the opensea calls counter or not. This
     * counter's intended purpose is that we cannot exceed our specified max
     * calls/sec and violate Opensea API TOS.
     *
     * @return bool
     */
    private function canIncrementOpensea(): bool
    {
        $counter = Cache::get('opensea_counter', function () {
            Cache::put('opensea_counter', 0);

            return 0;
        });

        return $counter < config('hawk.opensea.network.max_calls');
    }

    /**
     * Increments the total Opensea API call counter used to cap daily number
     * of API calls sent.
     *
     * @return void
     */
    private function incrementOpenseaAPICallCounter(): void
    {
        if (Cache::has('opensea_calls_count'))
            Cache::increment('opensea_calls_count');
        else
            Cache::put('opensea_calls_count', 1);
    }
}
