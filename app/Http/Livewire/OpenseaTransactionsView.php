<?php

namespace App\Http\Livewire;

use App\Traits\HandlesOpenseaEvents;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OpenseaTransactionsView extends Component
{
    use HandlesOpenseaEvents;

    public $wallet = '0x9FB952208007c6Ad7f9E756EC399812c57861d10';
    public ?string $cursor = null;
    public ?Collection $events = null;
    protected $queryString = ['wallet'];

    public $type = null;
    public $start = null;
    public $end = null;
    public bool $filtered = false;

    public function render()
    {
        return view('livewire.opensea-transactions-view');
    }

    /**
     * Invokes when the component is mounted and loads initial events for
     * provided wallet address.
     *
     * @return void
     */
    public function mount(): void
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

    /**
     * Loads additional events for API (if pagination cursor is provided) for
     * the wallet address being searched.
     *
     * @return void
     */
    public function load_more_events(): void
    {
        if (!($this->events instanceof Collection && $this->events->count() >= 20 && $this->cursor)) return;

        [
            'asset_events' => $events,
            'next'         => $cursor
        ] = static::get_events_from_opensea(
            $this->wallet,
            $this->cursor,
            $this->type,
            optional($this->start, fn ($date) => (int) (new Carbon($date))->format('U')),
            optional($this->end, fn ($date) => (int) (new Carbon($date))->format('U')),
        );

        $events = collect($events)->map(fn ($event) => static::parse_raw_event($event));
        ['uniques' => $uniques, 'existing' => $existing] = static::save_events($this->wallet, $events);

        $this->events = $this->events->concat($uniques->concat($existing));
        $this->cursor = $cursor ?: null;
    }

    /**
     * Applies selected filters and loads events for the API.
     *
     * @return void
     */
    public function apply_filters(): void
    {
        $type = $this->type;
        $start = optional($this->start, fn ($date) => (int) (new Carbon($date))->format('U'));
        $end = optional($this->end, fn ($date) => (int) (new Carbon($date))->format('U'));

        // No filters added
        if (!$type && !$start && !$end) return;

        [
            'asset_events' => $events,
            'next'         => $cursor,
            'query'        => $query,
        ] = static::get_events_from_opensea(
            $this->wallet,
            null,
            $type,
            $start,
            $end,
        );

        $events = collect($events)->map(fn ($event) => static::parse_raw_event($event));
        ['uniques' => $uniques, 'existing' => $existing] = static::save_events($this->wallet, $events);

        $this->events = $uniques->concat($existing);
        $this->cursor = $cursor ?: null;
        $this->filtered = true;
    }
}
