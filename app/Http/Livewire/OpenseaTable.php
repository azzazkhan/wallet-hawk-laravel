<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Traits\Opensea\HasCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\Opensea\ManagesEvents;
use App\Traits\Opensea\InteractsWithApi;
use App\Traits\Opensea\InteractsWithWallet;

class OpenseaTable extends Component
{
    use HasCounter, ManagesEvents, InteractsWithApi, InteractsWithWallet;

    /**
     * Wallet address being searched
     *
     * @var string
     */
    public $wallet;

    /**
     * Opensea event type filter
     *
     * @var ?string
     */
    public ?string $event_type;

    /**
     * Start date filter
     *
     * @var ?int
     */
    public ?int $start_date;

    /**
     * End date filter
     *
     * @var ?int
     */
    public ?int $end_date;

    /**
     * Opensea pagination cursor.
     *
     * @var ?string
     */
    public ?string $cursor = null;

    /**
     * Fetched and processed events
     *
     * @var \Illuminate\Support\Collection<\App\Models\Opensea>
     */
    public Collection $events;

    /**
     * Error message
     *
     * @var ?string
     */
    public ?string $error;

    /**
     * Query string synchronized with internal component state
     *
     * @var array<string>
     */
    protected $queryString = ['wallet'];

    /**
     * Livewire's provided function used instead of constructor.
     *
     * @return void
     */
    public function mount()
    {
        $this->getInitialEvents();
    }

    /**
     * Returns view for component UI content.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.opensea-table');
    }

    /**
     * Loads events for initial render.
     *
     * @return void
     */
    public function getInitialEvents(): void
    {
        Log::debug('Loading events for initial render');

        if ($this->hasCooledDown($this->wallet)) {
            Log::debug('Wallet has cooled down, fetching new records from API');

            $response = $this->getEventsFromAPI($this->wallet);

            ['events' => $events, 'uniques' => $uniques, 'existing' => $existing] = $this
                ->processEvents(
                    $this->wallet,
                    $response['asset_events']
                );

            Log::debug(
                sprintf('%d total events retrieved from API', $events->count()),
                compact('uniques', 'existing')
            );

            $this->updateLockout($this->wallet);

            $this->events = $events;
            $this->cursor = $response['next'];
            return;
        }

        Log::debug('Wallet has not cooled down yet, fetching from database');
        $this->events = $this
            ->getEventsQuery($this->wallet)
            ->get();

        Log::debug(sprintf('%d events found in database', $this->events->count()));
    }

    public function loadMoreEvents(): void
    {
        $this->error = null;

        Log::debug('Loading more events');

        if ($this->events->count() < config('hawk.opensea.event.per_page')) {
            Log::debug('Previous page size was small this means no new events');
            return;
        }

        // If wallet is indexed then fetch from database
        if ($this->isIndexed($this->wallet)) {
            Log::debug('Wallet is indexed, fetching from database');

            $events = $this
                ->getEventsQuery($this->wallet)
                ->where('event_timestamp', '<', $this->events->last()->event_timestamp)
                ->get();

            $this->events = $this
                ->events
                ->concat($events)
                ->sortByDesc('event_timestamp');

            return;
        }

        Log::debug('Wallet is not indexed fetching from API');

        $response = $this->getEventsFromAPI(
            $this->wallet,
            cursor: $this->cursor,
            before_date: !$this->cursor ? $this->events->last()->event_timestamp : null
        );

        Log::debug(sprintf('Received %d events from API', count($response['asset_events'])));

        ['events' => $events, 'uniques' => $uniques, 'existing' => $existing] = $this
            ->processEvents(
                $this->wallet,
                $response['asset_events']
            );

        // API returned fewer records, this means we have reached till end
        if (count($response['asset_events']) < config('hawk.opensea.event.per_page')) {
            Log::debug('API sent fewer events, this means we have reached till end');
            $this->setIndexed($this->wallet);
        }

        $this->events = $this
            ->events
            ->concat($events)
            ->sortByDesc('event_timestamp');

        Log::debug(sprintf('Total events are now %d', $this->events->count()));

        dd($this->events);
    }
}
