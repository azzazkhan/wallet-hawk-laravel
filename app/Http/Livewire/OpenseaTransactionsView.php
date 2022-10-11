<?php

namespace App\Http\Livewire;

use App\Models\Opensea;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Component;

class OpenseaTransactionsView extends Component
{
    public $wallet = '0x9FB952208007c6Ad7f9E756EC399812c57861d10';
    public ?string $cursor = null;
    public ?Collection $events = null;
    protected $queryString = ['wallet'];

    public function render()
    {
        return view('livewire.opensea-transactions-view');
    }

    public function mount()
    {
        [
            'asset_events' => $events,
            'next'         => $cursor
        ] = static::get_events_from_opensea($this->wallet);
        $events = collect($events)->map(fn ($event) => static::parse_raw_event($event));
        ['uniques' => $uniques, 'existing' => $existing] = static::save_events($this->wallet, $events);

        $this->events = $uniques->concat($existing);
        $this->cursor = $cursor;
    }

    private static function get_events_from_opensea(
        string $wallet,
        string $cursor = null,
        string $event_type = null,
        int $date_start = null,
        int $date_end = null,
    ): array {
        $query = ['account_address' => $wallet];
        $query = array_merge($query, optional($event_type, fn ($event_type) => compact('event_type')) ?: []);
        $query = array_merge($query, optional($cursor, fn ($cursor) => compact('cursor')) ?: []);
        $query = array_merge($query, optional($date_start, fn ($occurred_before) => compact('occurred_before')) ?: []);
        $query = array_merge($query, optional($date_end, fn ($occurred_after) => compact('occurred_after')) ?: []);

        $response = Http::retry(3, 300)
            ->acceptJson()
            ->withHeaders([
                "X-API-KEY" => config('hawk.opensea.api_key')
            ])
            ->get('https://api.opensea.io/api/v1/events', $query);

        return $response->json();
    }

    private static function parse_raw_event(mixed $event): ?array
    {
        // Should be an array or collection!
        if (!(is_array($event) || $event instanceof Collection)) return null;

        // Convert collection to array
        $event = $event instanceof Collection ? $event->toArray() : $event;

        $parsed = [
            'event_id'   => $event['id'] ?? 0,
            'event_type' => Str::lower($event['event_type'] ?? 'unknown'),
            'value'      => $event['total_price'] ?? 0,

            'accounts' => [
                'from'   => $event['from_account'] ?? null,
                'to'     => $event['to_account'] ?? null,
                'seller' => $event['seller'] ?? null,
                'owner'  => $event['owner_account'] ?? null,
                'winner' => $event['winner_account'] ?? null,
            ],

            'contract_address' => $event['contract_address'],
            'collection_slug'  => $event['collection_slug'],
            'created_date'     => optional(
                $event['created_date'],
                fn ($timestamp) => (int) (new Carbon($timestamp))->format('U')
            ),

            'event_timestamp' => optional(
                $event['event_timestamp'],
                fn ($timestamp) => (int) (new Carbon($timestamp))->format('U')
            ) ?: now(),

            'payment_token' => optional($event['payment_token'], function ($token): array {
                return [
                    'decimals' => is_numeric($token['decimals'] ?? null) ? (int) $token['decimals'] : 0,
                    'symbol'   => Str::upper($token['symbol'] ?? 'unknown'),
                    'eth'      => $token['eth_price'] ?? null,
                    'usd'      => $token['usd_price'] ?? null,
                ];
            })
        ];

        $parsed = array_merge($parsed, optional($event['asset'], function (array $asset): array {
            // Asset contract and schema
            $contract = optional($asset['asset_contract'], function (array $contract): array {
                return [
                    'schema' => Str::lower($contract['schema_name'] ?? 'unknown'),
                    'contract' => [
                        'name'         => $contract['name'] ?? 'unknown',
                        'address'      => $contract['address'] ?? 'unknown',
                        'type'         => $contract['asset_contract_type'] ?? 'unknown',
                        'image_url'    => $contract['image_url'] ?? null,
                        'created_date' => optional(
                            $contract['created_date'],
                            fn ($date) => (int) (new Carbon($date))->format('U')
                        ),
                    ],
                ];
            });

            // Static and animation URLs
            $media = [
                'image' => [
                    'url'       => $asset['image_url'] ?? null,
                    'preview'   => $asset['image_preview_url'] ?? null,
                    'thumbnail' => $asset['image_thumbnail_url'] ?? null,
                    'original'  => $asset['image_original_url'] ?? null,
                ],
                'animation' => [
                    'url'      => $asset['animation_url'] ?? null,
                    'original' => $asset['animation_original_url'] ?? null,
                ],
            ];

            // Owner collection
            $collection = optional($asset['collection'], function (array $collection): array {
                return [
                    'banner_image_url'   => $collection['banner_image_url'] ?? null,
                    'created_date'       => optional(
                        $collection['created_date'],
                        fn (string $date) => (int) (new Carbon($date))->format('U')
                    ),
                    'description'        => $collection['description'] ?? null,
                    'external_url'       => $collection['external_url'] ?? null,
                    'featured_image_url' => $collection['featured_image_url'] ?? null,
                    'image_url'          => $collection['image_url'] ?? null,
                    'large_image_url'    => $collection['large_image_url'] ?? null,
                    'name'               => $collection['name'] ?? null,
                    'slug'               => $collection['slug'] ?? null,
                ];
            });

            // Asset, media and contract
            return array_merge(
                $contract,
                compact('media', 'collection'),
                [
                    'asset' => [
                        'id'            => $asset['id'] ?? 0,
                        'name'          => $asset['name'] ?? 'Unknown',
                        'token_id'      => $asset['token_id'] ?? null,
                        'description'   => $asset['description'] ?? 'No description',
                        'external_link' => $asset['external_link'] ?? null,
                        'permalink'     => $asset['permalink'] ?? null,
                    ]
                ]
            );
        }) ?: []);

        return $parsed;
    }

    private static function save_events(string $wallet, Collection|array $events): Collection
    {
        $events = ($events instanceof Collection ? $events : collect($events))->unique('event_id');

        $existing = Opensea::forWallet($wallet)
            ->whereIn('event_id', $events->map(fn ($event) => $event['event_id'])->toArray())
            ->get();

        $existing_ids = $existing->pluck('event_id')->all();

        $uniques = $events
            ->filter(fn ($event) => !in_array($event['event_id'], $existing_ids))
            ->map(fn ($event) => Opensea::create(array_merge($event, ['wallet' => Str::lower($wallet)])));

        return new Collection(compact('existing', 'uniques'));
    }

    public static function prepare_event_for_preview(string $wallet, array|Opensea $event): Collection
    {
        $event = is_array($event) ? new Opensea($event) : $event;

        $from_account = $event->accounts->get('from')
            ?: $event->accounts->get('seller')
            ?: $event->accounts->get('owner');
        $to_account   = $event->accounts->get('to') ?: $event->accounts->get('winner');

        return collect([
            'name'       => $event->asset->get('name', 'Unnamed'),
            'image'      => optional(
                $event->media->get('image'),
                function (array $image): ?string {
                    return $image['original'] ?? $image['url'] ?? $image['preview'] ?? $image['thumbnail'] ?? null;
                }
            ),
            'animation'  => optional(
                $event->media->get('animation'),
                function (array $animation): ?string {
                    return $animation['original'] ?? $animation['url'] ?? null;
                }
            ),
            'direction'  => optional(
                $from_account,
                fn (array $account) => $account['address']
            ) === $wallet ? 'out' : (optional(
                $to_account,
                fn (array $account) => $account['address']
            ) === $wallet ?
                'in' : null
            ),
            'token_id'   => $event->asset->get('token_id'),
            'asset_id'   => $event->asset->get('id'),
            'event_id'   => $event->event_id,
            'from'       => $from_account,
            'to'         => $to_account,
            'owner'      => $event->accounts->get('owner'),
            'schema'     => $event->schema,
            'event_type' => preg_replace('/(_|-)/', ' ', preg_replace('/(successful)/', 'sale', $event->event_type)),
            'value'      => $event->value ? static::gweiToEth($event->value) : null,
            'timestamp'  => new Carbon($event->event_timestamp),
        ]);
    }

    private static function gweiToEth(int $gwei): float
    {
        return $gwei / 1000000000000000000;
    }

    public function load_more_events(): void
    {
        if (!($this->events instanceof Collection && $this->events->count() >= 20 && $this->cursor)) return;

        [
            'asset_events' => $events,
            'next'         => $cursor
        ] = static::get_events_from_opensea($this->wallet, $this->cursor);

        $events = collect($events)->map(fn ($event) => static::parse_raw_event($event));
        ['uniques' => $uniques, 'existing' => $existing] = static::save_events($this->wallet, $events);

        // dd($this->events->concat($uniques->concat($existing)));

        $this->events = $this->events->concat($uniques->concat($existing));
        $this->cursor = $cursor ?: null;
    }
}
