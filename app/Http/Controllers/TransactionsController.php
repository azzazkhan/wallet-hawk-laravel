<?php

namespace App\Http\Controllers;

use App\Helpers\JSON;
use App\Models\ERC20;
use App\Models\Opensea;
use App\Traits\HandlesOpenseaTransactions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionsController extends Controller
{
    use HandlesOpenseaTransactions;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'wallet' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'event'  => ['string', Rule::in(config('hawk.opensea.event.types'))]
        ]);

        $schema = $request->query('schema'); // Check which token user is searching for

        // Show ERC20 records if they are willing to see them
        if (strtolower($schema) === 'erc20')
            return view('transactions', [
                'schema'       => 'ERC20',
                'transactions' => JSON::parseFile('erc20.json')['transactions'],
            ]);


        return view('transactions', [
            'schema'       => 'ERC721-ERC1155',
            'transactions' => $this->fetchOpenseaEvents($validated['wallet'], $validated['event'] ?? null),
        ]);
    }

    private function walletRules()
    {
    }

    public function paginateOpensea(Request $request)
    {
        $request->validate([
            'wallet'     => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'event'      => ['string', Rule::in(config('hawk.opensea.event.types'))],
            'schema'     => ['string', Rule::in(['ERC20', 'opensea'])],

            // Previews records
            'previous'   => ['string', 'min:6', 'regex:/(([A-z0-9])=?){60,}$/'], // Cursor
            'before'     => ['numeric'], // Event ID
            // Newer records
            'next'       => ['string', 'min:6', 'regex:/(([A-z0-9])=?){60,}$/'], // Cursor
            'after'      => ['numeric'], // Event ID

            // Date filter
            'date_start' => ['numeric', 'datetime:U'],
            'date_end'   => ['numeric', 'datetime:U'],
        ]);

        // ERC20 transactions searched
        if ($request->has('schema') && strtoupper($request->query('schema')) == 'ERC20') {
            $ref_transaction = null;

            // If reference transaction is passed (for pagination) then fetch its
            // record first
            if ($request->has('before') || $request->has('after')) {
                // Fetch record based on passed event ID
                $ref_transaction = ERC20::query()
                    ->when($request->has('after'), function (Builder $query) use ($request) {
                        $query->where('block_id', $request->query('after'));
                    })
                    ->when($request->has('before'), function (Builder $query) use ($request) {
                        $query->where('block_id', $request->query('before'));
                    })
                    ->select('block_timestamp')
                    ->limit(config('hawk.opensea.event.per_page'))
                    ->first();

                // Reference transaction does not exists or has invalid timestamp
                // and we cannot use its event timestamp :(
                if (!$ref_transaction || !$ref_transaction->block_timestamp)
                    throw new NotFoundHttpException(
                        'The reference transaction does not exist or is invalid, please navigate to first page.'
                    );
            }
        }

        $ref_transaction = null;

        // User is filtering by date, filtering by date will be performed only
        // through API calls
        // Pagination will only be performed using cursors
        if (($request->has('date_start') || $request->has('date_end'))) {
            $dates = [
                'start' => (int) $request->query('date_start'),
                'end'   => (int) $request->query('date_end'),
            ];
            $has_dates = $dates['start'] && $dates['end'];

            // User can mess up while selecting date range so its better we
            // filter out values for safety
            $start = $has_dates ? max($dates['start'], $dates['end']) : null;
            $end   = $has_dates ? min($dates['start'], $dates['end']) : null;

            // Opensea cursor provided (paginate through API only)
            $response = $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event'),
                cursor: $request->query('next') ?: $request->query('previous'),
                before: $start,
                after: $end,
            );

            return view('transactions', [
                'next'         => $response['next'],
                'previous'     => $response['previous'],
                // Has moved to next page?
                'paginated'    => $response['previous'] ? true : false,
                'transactions' => $response['asset_events'],
            ]);
        }



        // If reference transaction is passed (for pagination) then fetch its
        // record first
        if ($request->has('before') || $request->has('after')) {
            // Fetch record based on passed event ID
            $ref_transaction = Opensea::query()
                ->when($request->has('after'), function (Builder $query) use ($request) {
                    $query->where('event_id', $request->query('after'));
                })
                ->when($request->has('before'), function (Builder $query) use ($request) {
                    $query->where('event_id', $request->query('before'));
                })
                ->select('event_timestamp')
                ->limit(config('hawk.opensea.event.per_page'))
                ->first();

            // Reference transaction does not exists or has invalid timestamp
            // and we cannot use its event timestamp :(
            if (!$ref_transaction || !$ref_transaction->event_timestamp)
                throw new NotFoundHttpException(
                    'The reference transaction does not exist or is invalid, please navigate to first page.'
                );


            // We have already indexed all the records
            if ($this->hasOpenseaIndexed($request->query('wallet'))) {

                // Pagination timer is exhausted fetch new records from API
                if ($this->hasOpenseaPaginationTimerExhausted($request->query('wallet'))) {
                    $response = $this->fetchFromOpenseaAPI(
                        wallet_id: $request->query('wallet'),
                        type: $request->query('event'),
                        cursor: $request->query('next') ?: $request->query('previous'),
                        before: $request->has('before') ? $ref_transaction?->event_id : null,
                        after: $request->has('after') ? $ref_transaction?->event_id : null,
                    );

                    // Save fetched transactions to database
                    $records = $this->saveRawOpenseaEvents($response['asset_events']);

                    // API sent some records that already exist locally, this
                    // means we have further data
                    if ($records['existing'] > 0)
                        $this->updateOpenseaPaginationTimer($request->query('wallet'));

                    return view('transactions', [
                        'next'         => $response['next'],
                        'previous'     => $response['previous'],
                        // Has moved to next page?
                        'paginated'    => $response['previous'] ? true : false,
                        'transactions' => $response['asset_events'],
                    ]);
                }

                // Pagination timer is not exhausted yet, fetch records from DB
                $transactions = Opensea::forWallet(
                    $request->query('wallet'),
                    $request->query('event'),
                    config('hawk.opensea.event.per_page')
                )->where(function (Builder $query) use ($request, $ref_transaction) {
                    if ($request->has('before'))
                        $query->where('event_timestamp', '<', $ref_transaction->event_timestamp);
                    if ($request->has('after'))
                        $query->where('event_timestamp', '>', $ref_transaction->event_timestamp);

                    return $query;
                });
            }
        }
    }
}
