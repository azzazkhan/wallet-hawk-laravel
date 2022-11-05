<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Opensea;
use App\Traits\HandlesOpenseaEvents;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OpenseaController extends Controller
{
    use HandlesOpenseaEvents;

    public function __invoke(Request $request)
    {
        logger('Received request for Opensea events', $request->toArray());

        $request->validate([
            'address' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'type'   => ['nullable', 'string', Rule::in(config('hawk.opensea.event.types'))],
            'start_date' => ['nullable', 'string', 'date_format:d-m-Y'],
            'end_date' => ['nullable', 'string', 'date_format:d-m-Y'],
            'cursor' => ['nullable', 'string', 'min:20']
        ]);

        logger('The request passed validation');

        [
            'asset_events' => $events,
            'next'         => $cursor,
            'query'        => $query,
        ] = static::get_events_from_opensea(
            wallet: request()->query('address'),
            cursor: request()->query('cursor'),
            event_type: request()->query('type'),
            date_start: optional(request()->query('start_date'), fn ($date) => (new Carbon($date))->timestamp),
            date_end: optional(request()->query('end_date'), fn ($date) => (new Carbon($date))->timestamp)
        );

        logger('Opensea events query', $query);
        logger(sprintf('Got %s events from Opensea API', count($events)));

        $events = collect($events)->map(fn ($event) => static::parse_raw_event($event));
        [
            'uniques' => $uniques,
            'existing' => $existing
        ] = static::save_events($request->query('address'), $events);

        logger(
            sprintf(
                'Filtered and got %s unique and %s already existing events in database',
                $uniques->count(),
                $existing->count(),
            )
        );

        $events = $uniques->concat($existing)->map(function (Opensea $event) use ($request) {
            return static::prepare_event_for_preview($request->query('address'), $event);
        })->values();

        return response()
            ->json([
                'success' => true,
                'status'  => Response::HTTP_OK,
                'data'    => compact('events', 'cursor'),
            ]);
    }
}
