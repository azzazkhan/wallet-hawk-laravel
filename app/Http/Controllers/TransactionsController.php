<?php

namespace App\Http\Controllers;

use App\Helpers\JSON;
use App\Models\ERC20;
use App\Models\Opensea;
use App\Models\Wallet;
use App\Traits\HandlesERC20Transactions;
use App\Traits\HandlesOpenseaTransactions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionsController extends Controller
{
    use HandlesOpenseaTransactions, HandlesERC20Transactions;

    public function index(Request $request)
    {
        $request->validate([
            'wallet'     => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'event'      => ['string', Rule::in(array_merge(config('hawk.opensea.event.types'), ['all']))],
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
        if ($request->has('schema') && strtolower($request->query('schema')) == 'erc20') {
            return view('transactions', [
                'schema'       => 'ERC20',
                'transactions' => JSON::parseFile('erc20.json')['transactions'],
            ]);
        }

        /**
         * ===================================
         * OPENSEA TRANSACTIONS LOGIC
         * ===================================
         */
        Log::debug('Showing opensea transactions', [
            'event_type' => $request->query('event'),
        ]);

        $ref_transaction = null;

        // ===== DATE FILTER =====
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

            Log::debug('Filtering transactions by date', [
                'start'     => (new Carbon($start))->format('d-m-y H:i:s'),
                'end'       => (new Carbon($end))->format('d-m-y H:i:s'),
                'paginated' => $request->has('next') || $request->has('previous'),
                'next'      => $request->query('next'),
                'previous'  => $request->query('previous'),
            ]);

            // Opensea cursor provided (paginate through API only)
            $response = $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event'),
                cursor: $request->query('next') ?: $request->query('previous'),
                before: $start,
                after: $end,
            );


            $records = $this->saveRawOpenseaEvents($response['asset_events']);

            Log::debug('Fetched events form API and saved to database', [
                'fetched'  => count($response['asset_events']),
                'existing' => $records['existing'],
                'uniques'  => $records['uniques']
            ]);

            return view('transactions', [
                'next'          => $response['next'],
                'previous'      => $response['previous'],
                // Has moved to next page?
                'paginated'     => $response['previous'] ? true : false,
                'transactions'  => $records['events'],
                'date_filtered' => true
            ]);
        }

        // ===== PAGINATION LOGIC =====
        // If reference transaction is passed (for pagination) then fetch its
        // record first
        if ($request->has('before') || $request->has('after')) {
            Log::debug('Request has pagination details', [
                'before' => $request->query('before'),
                'after'  => $request->query('after'),
            ]);

            // Make sure wallet record exist for the transaction we're looking
            // for, this should exist if user has navigated through pagination
            if (!Wallet::where('wallet_id', $request->query('wallet'))->first())
                return abort(401, 'Wallet record does not exist, please go to first page.');


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

            // User is trying to view newer records and we'll surely have them
            // because user can only come to this page through pagination and
            // we've already saved all previous records locally
            if ($request->has('after')) {
                $events = Opensea::forWallet(
                    $request->query('wallet'),
                    $request->query('event'),
                    config('hawk.opensea.event.per_page')
                )
                    ->where('event_timestamp', '>', (int) $ref_transaction->event_timestamp->format('U'))
                    ->get();

                // Grab the first record for wallet for comparing if we have
                // further new records or not
                $first_one = Opensea::forWallet(
                    $request->query('wallet'),
                    $request->query('event')
                )
                    ->first();

                // First record of specified filters query is equal to fetched
                // list of records of specified filters
                // Query(filters)->first  ===  Query(filters)(bulk)->first
                $page_one = $first_one->transaction_id !== $events->first()->transaction_id;

                Log::debug('Requested for newer events, served from database', [
                    'events_count' => $events->count(),
                    'page_one'     => $page_one,
                ]);

                return view('transactions', [
                    // Has moved to next page?
                    'paginated'    => $page_one,
                    'transactions' => $events,
                ]);
            }

            // We have already indexed all the records
            if ($this->hasOpenseaIndexed($request->query('wallet'))) {

                // Pagination timer is exhausted fetch new records from API
                if ($this->hasOpenseaPaginationTimerExhausted($request->query('wallet'))) {
                    $response = $this->fetchFromOpenseaAPI(
                        wallet_id: $request->query('wallet'),
                        type: $request->query('event'),
                        before: (int) $ref_transaction->event_timestamp->format('U'),
                    );

                    // Save fetched transactions to database
                    $records = $this->saveRawOpenseaEvents($response['asset_events']);

                    Log::debug('Requested for previous records, serving from API', [
                        'fetched'                    => count($response['asset_events']),
                        'existing'                   => $records['existing'],
                        'uniques'                    => $records['uniques'],
                        'indexed'                    => true,
                        'pagination_timer_exhausted' => true,
                    ]);

                    // User is viewing previous records and some of the records
                    // already exist in database, this means further records
                    // will surely exist
                    if ($records['existing'] > 0)
                        $this->updateOpenseaPaginationTimer($request->query('wallet'));

                    return view('transactions', [
                        'paginated'    => true, // We must have came from first page
                        'transactions' => $records['events'],
                    ]);
                }

                // Pagination timer is not exhausted yet, fetch records from DB
                $events = Opensea::forWallet(
                    $request->query('wallet'),
                    $request->query('event'),
                    config('hawk.opensea.event.per_page')
                )
                    ->where('event_timestamp', '<', (int) $ref_transaction->event_timestamp->format('U'))
                    ->get();

                Log::debug('Requested for previous records, serving from database', [
                    'events_count'               => $events->count(),
                    'indexed'                    => true,
                    'pagination_timer_exhausted' => false,
                ]);

                return view('transactions', [
                    // Has moved to next page?
                    'paginated'    => true, // We must have came from first page
                    'transactions' => $events,
                ]);
            }

            // We have not indexed all transactions for this wallet, data for
            // all requests will be consumed from API
            $response = $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event'),
                before: (int) $ref_transaction->event_timestamp->format('U'),
            );

            $records = $this->saveRawOpenseaEvents($response['asset_events']);

            Log::debug('Requested for previous records, not indexed so serving through API', [
                'fetched'                    => count($response['asset_events']),
                'existing'                   => $records['existing'],
                'uniques'                    => $records['uniques'],
                'indexed'                    => false,
            ]);

            // We API sent fewer records then it means we have reached till end
            // are there are no further records
            if ($records['events']->count() < config('hawk.opensea.event.per_page'))
                $this->setOpenseaIndexed($request->query('wallet'));

            return view('transactions', [
                'paginated' => true,
                'transactions'    => $records['events']
            ]);
        }

        Log::debug('Requested for simple transactions (without pagination)');

        // ===== SIMPLE VIEW =====
        // We have indexed all the records all ready
        if ($this->hasOpenseaIndexed($request->query('wallet'))) {

            // Index timer has expired fetch new records
            if ($this->hasOpenseaPaginationTimerExhausted($request->query('wallet'))) {
                $response = $this->fetchFromOpenseaAPI(
                    wallet_id: $request->query('wallet'),
                    type: $request->query('event'),
                );

                // Save fetched transactions to database
                $records = $this->saveRawOpenseaEvents($response['asset_events']);

                // Some of the records already exist locally so further records
                // will exist definitely because we have indexed till end
                if ($records['existing'] > 0)
                    $this->updateOpenseaPaginationTimer($request->query('wallet'));

                Log::debug('Indexed and timer has exhausted so fetching events from API and saving', [
                    'fetched'                    => count($response['asset_events']),
                    'existing'                   => $records['existing'],
                    'uniques'                    => $records['uniques'],
                    'indexed'                    => true,
                    'pagination_timer_exhausted' => true,
                    'updated_pagination_timer'   => $records['existing'] > 0
                ]);

                return view('transactions', [
                    'transactions' => $records['events'],
                ]);
            }

            // Pagination timer is not exhausted yet, fetch records from DB
            $events = Opensea::forWallet(
                $request->query('wallet'),
                $request->query('event'),
                config('hawk.opensea.event.per_page')
            )
                ->get();

            Log::debug('Indexed and timer has not exhausted, serving from database', [
                'events_count'               => $events->count(),
                'indexed'                    => true,
                'pagination_timer_exhausted' => false,
            ]);

            return view('transactions', [
                'paginated'    => true, // We must have came from first page
                'transactions' => $events,
            ]);
        }

        // Records are not indexed yet, if cool down timer has exhausted then
        // then fetch records from API and increment the timer
        if ($this->hasOpenseaCooledDown($request->query('wallet'))) {
            $response = $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event')
            );

            $records = $this->saveRawOpenseaEvents($response['asset_events']);

            // Update the cool down timer
            $this->updateOpenseaLockoutTimer($request->query('wallet'));

            Log::debug('Not fully indexed yet but cooled down, fetching events from API and saving', [
                'fetched'                => count($response['asset_events']),
                'existing'               => $records['existing'],
                'uniques'                => $records['uniques'],
                'indexed'                => false,
                'rate_limiter_exhausted' => true,
            ]);

            return view('transactions', ['transactions' => $records['events']]);
        }

        // Timer has not cooled down yet, fetch records from database
        $events = Opensea::forWallet(
            $request->query('wallet'),
            $request->query('event'),
            config('hawk.opensea.event.per_page')
        )
            ->get();

        Log::debug('Not fully indexed yet and not cooled down, serving from database', [
            'events_count'           => $events->count(),
            'rate_limiter_exhausted' => false,
        ]);

        return view('transactions', ['transactions' => $events]);
    }

    public function etherscan(Request $request)
    {
        if ($this->hasEtherscanIndexed($request->query('wallet'))) {
            if ($this->hasEtherscanCooledDown($request->query('wallet'))) {
            }
        }

        $transactions = $this->fetchFromEtherscanAPI($request->query('wallet'));

        return view('transactions', compact('transactions'));

        // ERC20::query()
        //     ->where(function (Builder $query) use ($request) {
        //         $query
        //             ->where('accounts->from', $request->query('wallet'))
        //             ->orWhere('accounts->to', $request->query('wallet'));
        //     })
        //     ->limit(20)
        //     ->get();
    }
}
