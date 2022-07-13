<?php

namespace App\Http\Livewire;

use CStr;
use App\Models\Opensea;
use Livewire\Component;
use Illuminate\Support\Carbon;
use App\Traits\Opensea\HasCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\Opensea\ManagesEvents;
use App\Traits\Opensea\InteractsWithApi;

class OpenseaTable extends Component
{
    use HasCounter, ManagesEvents, InteractsWithApi;

    /**
     * Wallet address being searched
     *
     * @var string
     */
    public $wallet;

    /**
     * Fetched and processed events
     *
     * @var \Illuminate\Support\Collection<\App\Models\Opensea>
     */
    public Collection $events;

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
    }

    public function getMoreEvents(): void
    {
    }

    /**
     * Adds additional details to `\App\Models\Opensea` model instance for
     * accessing on frontend.
     *
     * @param string $wallet
     * @param \App\Models\Opensea $event
     *
     * @return \App\Models\Opensea
     */
    private function convertTokenForView(string $wallet, Opensea $event): Opensea
    {
        $image            = $event['media']['image'];
        $event->thumbnail = ($image['thumbnail'] ?: $image['url']) ?: $image['original'];
        $event->name      = $event->asset['name'];
        $event->direction = $event->accounts['to'] == $wallet || $event->accounts['winner'] == $wallet
            ? 'IN' : 'OUT';
        $event->from      = $event->accounts['from'] ?: $event->accounts['seller'];
        $event->to        = $event->accounts['to'] ?: $event->accounts['winner'];
        $event->value     = '0 ETH, 0 USD';
        $event->timestamp = new Carbon($event->event_timestamp);

        if (CStr::isValidArray($event->payment_token))
            $event->value = sprintf(
                '%s ETH, %s USD',
                substr($event->payment_token['eth'], 0, 6),
                substr($event->payment_token['usd'], 0, 6)
            );

        return $event;
    }
}
