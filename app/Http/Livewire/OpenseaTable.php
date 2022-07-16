<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Traits\Opensea\HasCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\Opensea\ManagesEvents;
use App\Traits\Opensea\InteractsWithApi;
use App\Traits\Opensea\InteractsWithWallet;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
    public ?string $event_type = null;

    /**
     * Start date filter
     *
     * @var ?string
     */
    public ?string $start_date = null;

    /**
     * End date filter
     *
     * @var ?string
     */
    public ?string $end_date = null;

    /**
     * Opensea pagination cursor.
     *
     * @var ?string
     */
    public ?string $cursor = null;

    /**
     * Records are filtered or not.
     *
     * @var bool
     */
    public bool $filtered = false;

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
     * Information message
     *
     * @var ?string
     */
    public ?string $message;

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
        $this->error = null;

        Log::debug('Loading events for initial render');

        if ($this->hasCooledDown($this->wallet)) {
            Log::debug('Wallet has cooled down, fetching new records from API');

            try {
                $response = $this->getEventsFromAPI($this->wallet);
            } catch (ServiceUnavailableHttpException $error) {
                $this->error = "Too much traffic, please try again in few minutes";
                return;
            } catch (TooManyRequestsHttpException $error) {
                $this->error = "Too many requests, please try again in few seconds";
                return;
            }

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
        $this->message = null;

        Log::debug('Loading more events');

        if ($this->events->count() < config('hawk.opensea.event.per_page')) {
            Log::debug('Previous page size was small this means no new events');
            $this->message = "No more data to load";
            return;
        }

        if ($this->filtered) {
            $filters = $this->getFilters();

            try {
                $response = $this->getEventsFromAPI(
                    $this->wallet,
                    type: $filters['event_type'],
                    cursor: $this->cursor,
                    before_date: max($filters['start_date'], $filters['end_date']),
                    after_date: min($filters['start_date'], $filters['end_date']),
                );
            } catch (ServiceUnavailableHttpException $err) {
                $this->error = "Too much traffic, please try again in few minutes";
                return;
            } catch (TooManyRequestsHttpException $err) {
                $this->error = "Too many requests, please try again in few seconds";
                return;
            } catch (HttpException $err) {
                $this->error = "Record server sent an invalid response!";
                return;
            }

            ['events' => $events] = $this->processEvents($this->wallet, $response['asset_events']);

            $this->events = $events;
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

        try {
            $response = $this->getEventsFromAPI(
                $this->wallet,
                cursor: $this->cursor,
                before_date: !$this->cursor ? $this->events->last()->event_timestamp : null
            );
        } catch (ServiceUnavailableHttpException $err) {
            $this->error = "Too much traffic, please try again in few minutes";
            return;
        } catch (TooManyRequestsHttpException $err) {
            $this->error = "Too many requests, please try again in few seconds";
            return;
        } catch (HttpException $err) {
            $this->error = "Record server sent an invalid response!";
            return;
        }


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
    }

    public function filterEvents(): void
    {
        $this->filtered = true;

        $start_date = $this->start_date ? (int) (new Carbon($this->start_date))->format('U') : null;
        $end_date = $this->start_date ? (int) (new Carbon($this->end_date))->format('U') : null;
        $event_type = $this->event_type && in_array($this->event_type, config('hawk.opensea.event.types'))
            ? $this->event_type
            : null;

        try {
            $response = $this->getEventsFromAPI(
                $this->wallet,
                type: $event_type,
                before_date: max($start_date, $end_date),
                after_date: min($start_date, $end_date),
            );
        } catch (ServiceUnavailableHttpException $err) {
            $this->error = "Too much traffic, please try again in few minutes";
            return;
        } catch (TooManyRequestsHttpException $err) {
            $this->error = "Too many requests, please try again in few seconds";
            return;
        } catch (HttpException $err) {
            $this->error = "Record server sent an invalid response!";
            return;
        }

        ['events' => $events] = $this->processEvents($this->wallet, $response['asset_events']);

        $this->events = $events;
    }

    private function getFilters(): array
    {
        return [
            'start_date' => $this->start_date ? (int) (new Carbon($this->start_date))->format('U') : null,
            'end_date'   => $this->start_date ? (int) (new Carbon($this->end_date))->format('U') : null,
            'event_type' => $this->event_type && in_array($this->event_type, config('hawk.opensea.event.types'))
                ? $this->event_type
                : null
        ];
    }
}
