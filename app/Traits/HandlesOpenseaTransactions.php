<?php

namespace App\Traits;

use App\Models\Opensea;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

trait HandlesOpenseaTransactions
{
    private function fetchOpenseaEvents(string $wallet_id, string $event_type = null): Collection|null
    {
        if ($this->hasOpenseaCooledDown($wallet_id)) {
            // Fetch transactions for requested wallet address
            $response = $this->fetchFromOpenseaAPI($wallet_id, $event_type);

            // Invalid data or API calls limit reached
            if (is_null($response) || !array_key_exists('asset_events', $response))
                throw new TooManyRequestsHttpException(1, 'Please try again in a few moments');

            $result = $this->saveRawOpenseaEvents($response['asset_events']);

            $this->updateOpenseaLockoutTimer($wallet_id);

            return $result['events'];
        }

        // Fetch events from database
        return Opensea::forWallet($wallet_id, $event_type, config('hawk.opensea.event.per_page'))
            ->get();
    }

    private function fetchFromOpenseaAPI(string $wallet_id, string $type = null): array|null
    {
        if (!$this->incrementOpenseaCounter()) return null;

        $response = Http::retry(3, 300)
            ->acceptJson()
            ->withHeaders([
                "X-API-KEY" => config('hawk.opensea.api_key')
            ])
            ->get('https://api.opensea.io/api/v1/events', [
                'account_address' => $wallet_id,
                'event_type'      => $type
            ]);

        $this->decrementOpenseaCounter();

        if ($response->serverError())
            return null;

        return $response->json();
    }

    /**
     * ========================================
     * UTILITY FUNCTIONS (PARSING, SAVING...)
     * ========================================
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

    private function saveRawOpenseaEvents(array $events): array
    {
        // Parsed events into indexable form
        $events = collect($events)
            ->map(fn ($event) => $this->parseOpenseaEvent($event));

        return $this->saveOpenseaEvents($events);
    }

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
    private function hasOpenseaCooledDown(string $wallet_id): bool
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        // The wallet has never been searched before
        if (!$wallet) return true;

        return (int) $wallet->last_opensea_request->format('U') + config('hawk.opensea.limits.default') <=
            (int) now()->format('U');
    }

    private function updateOpenseaLockoutTimer(string $wallet_id)
    {
        $wallet = Wallet::where('wallet_id', $wallet_id)->first();

        if (!$wallet)
            return Wallet::create([
                'wallet_id' => $wallet_id,
                'last_opensea_request' => now()->format('Y-m-d H:i:s')
            ]);

        return $wallet->update([
            'last_opensea_request' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function updateOpenseaPaginationTimer(Wallet $wallet)
    {
    }


    /**
     * ==============================
     * API CALLS COUNTER MANAGEMENT
     * ==============================
     */
    private function incrementOpenseaCounter(): bool
    {
        if (!$this->canIncrementOpensea()) return false;
        Cache::increment('opensea_counter');

        return true;
    }

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

    private function canIncrementOpensea(): bool
    {
        $counter = Cache::get('opensea_counter', function () {
            Cache::put('opensea_counter', 0);

            return 0;
        });

        return $counter < config('hawk.opensea.network.max_calls');
    }
}
