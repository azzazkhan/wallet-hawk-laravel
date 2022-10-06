<?php

namespace App\Traits\Opensea;

use App\Models\Opensea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait ManagesEvents
{
    /**
     * Parses collection of raw Opensea asset events (fetched from API) using
     * `parseEvent` method, saves them using `saveEvens` method and returns
     * the result, a collection containing saved records, unique and existing
     * records count.
     *
     * @param string $wallet
     * @param \Illuminate\Support\array<array> $events
     *
     * @return \Illuminate\Support\Collection<mixed>
     */
    private function processEvents(string $wallet, array $events): Collection
    {
        return $this->saveEvents(
            $wallet,
            collect($events)->map(function ($event) use ($wallet) {
                return $this->parseEvent($wallet, $event);
            })
        );
    }

    /**
     * Parses and formats raw Opensea asset event (fetched from Opensea API)
     * and converts them into formatted `\App\Models\Opensea` model
     * compatible schema array.
     *
     * @param string $wallet
     * @param array<mixed> $event
     *
     * @return array<mixed>
     */
    private function parseEvent(string $wallet, array $event): array
    {
        // General event info (required for each event type)
        $parsed = [
            'event_id'   => $event['id'] ?? 0,
            'event_type' => Str::lower($event['event_type'] ?? 'unknown'),
            'wallet'     => Str::lower($event['wallet'] ?? 'unknown'),
            'value'      => $event['total_price'] ?? 0,

            // Related accounts (no need of parsing)
            'accounts' => [
                'from'   => $event['from_account'] ?? null,
                'to'     => $event['to_account'] ?? null,
                'seller' => $event['seller'] ?? null,
                'winner' => $event['winner_account'] ?? null,
            ]
        ];

        // Convert the event time (UTC string) to UNIX timestamp
        $parsed['event_timestamp'] = optional($event['event_timestamp'], function ($timestamp) {
            return (int) (new Carbon($timestamp))->format('U');
        });

        // Parse event asset if present (not in all events)
        $parsed = array_merge($parsed, optional($event['asset'], function (array $asset) use ($parsed): array {
            // Parse asset contract if present
            $contract = optional($asset['asset_contract'], function (array $contract) use ($parsed): array {
                $schema = Str::lower($contract['asset_schema'] ?? 'unknown');

                // Parse event asset contract and prevent null value exceptions.
                return array_merge(compact('schema'), [
                    'contract' => [
                        'address' => $contract['address'] ?? 'Unknown',
                        'type'    => $contract['asset_contract_type'] ?? 'Unknown',
                        'date'    => optional($contract['created_date'], function ($date): string {
                            return (new Carbon($date))->format('Y-m-d H:i:s');
                        }),
                    ],
                ]);
            });

            // Parse media (image and animation) with null error mitigation
            $media = [
                'image' => [
                    'url'       => $asset['image_url'] ?? null,
                    'original'  => $asset['image_original_url'] ?? null,
                    'preview'   => $asset['image_preview_url'] ?? null,
                    'thumbnail' => $asset['image_thumbnail_url'] ?? null,
                ],
                'animation' => [
                    'url'      => $asset['animation_url'] ?? null,
                    'original' => $asset['animation_original_url'] ?? null,
                ],
            ];

            // Merge asset related data with null value mitigation
            return array_merge($contract, compact('media'), [
                'asset' => [
                    'id' => (int) ($asset['id'] ?? '0'),
                    'name'          => $asset['name'] ?? 'Unknown',
                    'description'   => $asset['description'] ?? 'No description',
                    'external_link' => $asset['external_link'] ?? null,
                ]
            ]);
        }));

        $parsed['payment_token'] = optional($event['payment_token'], function ($token): array {
            $decimals = is_numeric($token['decimals'] ?? null) ? (int) $token['decimals'] : 0;
            $symbol   = Str::upper($token['symbol'] ?? 'unknown');
            $eth      = $token['eth_price'] ?? null;
            $usd      = $token['usd_price'] ?? null;

            return compact('decimals', 'symbol', 'eth', 'usd');
        });

        return $parsed;
    }

    /**
     * Saves passed formatted Opensea events into database neglecting existing
     * records. Returns a collection containing unique records count, existing
     * records count and collection of all passed event eloquent models
     * fetched/saved in database.
     *
     * @param string $wallet
     * @param \Illuminate\Support\Collection<array> $events
     *
     * @return \Illuminate\Support\Collection<mixed>
     */
    private function saveEvents(string $wallet, Collection $events): Collection
    {
        Log::debug(sprintf('Received %d events for saving in database', $events->count()));

        // Check if any of passed events already exist in database or not
        $existing_events = Opensea::query()
            ->where('wallet', $wallet)
            ->whereIn(
                'event_id',
                $events
                    ->unique('event_id')
                    ->map(fn ($transaction) => $transaction['event_id'])
                    ->toArray()
            )
            ->get();

        Log::debug(sprintf('Found %d existing events in database', $existing_events->count()));

        // If no record exists then save and return them
        if (!$existing_events || $existing_events->count() === 0) {
            Log::debug('All events are unique and do no exist in database');

            return new Collection([
                'uniques'      => count($events),
                'existing'     => 0,
                'events'       => $events->unique('event_id')->map(function (array $event) {
                    return Opensea::create($event);
                }),
            ]);
        }

        // Grab event ID from existing records (for filtering)
        $existing_ids = $existing_events
            ->map(fn (Opensea $event) => $event['event_id']);

        $uniques = $events
            ->filter(fn (array $event) => !$existing_ids->contains($event['event_id']));

        Log::debug(sprintf('There are %d unique events', $uniques->count()));

        // All records already exist in database, return those
        if (!$uniques || $uniques->empty()) {
            Log::debug('All events already exist in database');

            return new Collection([
                'events'       => $existing_events,
                'existing'     => $existing_events->count(),
                'uniques'      => 0,
            ]);
        }

        // No record exists locally and all passed records are unique
        return new Collection([
            'transactions' => array_merge(
                $existing_events,
                Opensea::create($uniques->unique('event_id')->toArray())
            ),
            'existing'     => $existing_events->count(),
            'uniques'      => $uniques->count(),
        ]);
    }

    /**
     * Returns Eloquent Query Builder for fetching events with address
     * conditional and record limiting already applied.
     *
     * @param string $wallet
     * @param ?int $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getEventsQuery(
        string $wallet,
        ?string $type = null,
        ?int $limit = 0
    ): EloquentBuilder {
        return Opensea::query()
            // Grab records for passed wallet address
            ->where('wallet', $wallet)
            // If event type is specified then grab events of only specified event type
            ->when(
                is_string($type) && in_array($type, config('hawk.opensea.event.types')),
                fn (EloquentBuilder $builder) => $builder->where('event_type', $type)
            )
            // Sort by occurrence time
            ->orderBy('event_timestamp', 'desc')
            // If limit is specified then limit the records
            ->limit($limit ?: config('hawk.opensea.event.per_page'));
    }
}
