<?php

namespace App\Http\Livewire;

use App\Models\Wallet;
use Livewire\Component;
use App\Models\Etherscan;
use Illuminate\View\View;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class EtherscanTable extends Component
{
    /**
     * Wallet address being searched
     *
     * @var string
     */
    public $wallet;

    /**
     * Fetched and processed transactions
     *
     * @var \Illuminate\Support\Collection<\App\Models\Etherscan>
     */
    public Collection $transactions;

    /**
     * Query string synchronized with internal component state
     *
     * @var array<string>
     */
    protected $queryString = ['wallet'];

    /**
     * Returns view for component UI content.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('livewire.etherscan-table');
    }

    /**
     * Livewire's provided function used instead of constructor.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->loadTransactions();
    }

    /**
     * Loads transactions in for initial render.
     *
     * @return void
     */
    private function loadTransactions(): void
    {
        // First load transactions from database
        $this->transactions = $this
            ->getTransactionsQuery()
            ->get()
            ->map(function (Etherscan $transaction) {
                return $this->convertTokenForView($transaction);
            })
            ->sortBy('block_number')
            ->unique('hash');

        // We have transaction records
        if ($this->transactions->isNotEmpty()) return;

        // We do not have transaction records so fetch from API and store them
        // in database
        $this->processTransactions(
            $this->getTransactionsFromAPI()
        );

        // We have fetched records stored in database now, we can now query
        // them
        $this->transactions = $this
            ->getTransactionsQuery()
            ->get()
            ->map(function (Etherscan $transaction) {
                return $this->convertTokenForView($transaction);
            })
            ->sortBy('block_number')
            ->unique('hash');
    }

    /**
     * Called through frontend action, loads next set of transactions.
     *
     * @return void
     */
    public function loadMoreTransactions(): void
    {
        // If current record set has fewer transactions than page size then do
        // not load more records
        if ($this->transactions->isEmpty() || $this->transactions->count() < config('hawk.etherscan.blocks.per_page'))
            return;

        // Get transactions from database prior to current transactions list's
        // last record
        $transactions = $this
            ->getTransactionsQuery()
            ->where('block_number', '<', $this->transactions->last()->block_number)
            ->get();

        // If we have fewer records than page size then load more from the API
        if ($transactions->count() < config('hawk.etherscan.blocks.per_page'))
            // If database returned zero records then get last block number
            // from last pagination set and use its block number as cursor for
            // API level paginaton
            $this->processTransactions(
                $this->getTransactionsFromAPI(
                    $transactions->isNotEmpty()
                        ? $transactions->last()->block_number
                        : $this->transactions->last()->block_number
                )
            );

        $this->transactions = $this
            ->transactions
            ->concat(
                // If fewer records were returned then load the transactions
                // fetched from API, saved in database
                $transactions->count() < config('hawk.etherscan.blocks.per_page')
                    ? $this
                    ->getTransactionsQuery()
                    ->where('block_number', '<', $this->transactions->last()->block_number)
                    ->get()
                    : $transactions // If not use the already fetched records
            )
            ->map(function (Etherscan $transaction) {
                return $this->convertTokenForView($transaction);
            })
            ->sortBy('block_number')
            ->unique('hash');
    }

    /**
     * Adds additional details to `\App\Models\Etherscan` model instance for
     * accessing on frontend.
     *
     * @param \App\Models\Etherscan $transaction
     *
     * @return \App\Models\Etherscan
     */
    private function convertTokenForView(Etherscan $transaction): Etherscan
    {
        // Calculate quantity using value and decimals
        $transaction->quantity = $this->calculateQuantity(
            $transaction->value,
            $transaction->token['decimals']
        );

        // Convert fee in Gwei to Ether
        $transaction->fee = round($this->gweiToEth($transaction->gas['price']), 3);

        // Convert timestamp number to `\Illuminate\Support\Carbon` instance
        // $transaction->block_timestamp = new Carbon($transaction->block_timestamp);

        return $transaction;
    }

    /**
     * Calculates ERC20 token quantity using block value and decimal count.
     *
     * @param int $value
     * @param ?int $decimals
     *
     * @return float
     */
    private function calculateQuantity(int $value, ?int $decimals = 0): float
    {
        if (!$value || !$decimals) return 0;

        return round($value / (pow(10, $decimals)), 3);
    }

    /**
     * Converts passed Gwei amount to ETH.
     *
     * @param int $gwei
     *
     * @return float
     */
    private function gweiToEth(int $gwei): float
    {
        return $gwei / 1000000000;
    }

    /**
     * Converts passed Wei amount to ETH.
     *
     * @param int $wei
     *
     * @return float
     */
    private function weiToEth(int $wei): float
    {
        return $wei / 1000000000000000000;
    }

    /**
     * Checks if wallet record exists for stored wallet ID or not.
     *
     * @return bool
     */
    private function walletExists(): bool
    {
        return Wallet::where('wallet_id', $this->wallet)->count() > 0;
    }

    /**
     * Parses collection of raw ERC20 transactions (fetched from API) using
     * `parseTransaction` method, saves them using `saveTransactions` method
     * and returns the result, a collection containing saved records, unique
     * and existing records count.
     *
     * @param \Illuminate\Support\Collection<array> $transactions
     *
     * @return \Illuminate\Support\Collection<mixed>
     */
    private function processTransactions(Collection $transactions): Collection
    {
        return $this->saveTransactions(
            $transactions->map(function ($transaction) {
                return $this->parseTransaction($transaction);
            })
        );
    }

    /**
     * Returns Eloquent Query Builder instance with all conditions applied for
     * wallet address.
     *
     * @param ?int $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getTransactionsQuery(?int $limit = 0): EloquentBuilder
    {
        return Etherscan::query()
            ->where(function (EloquentBuilder $query) {
                return $query
                    ->where('accounts->from', $this->wallet)
                    ->orWhere('accounts->to', $this->wallet);
            })
            ->orderBy('block_number', 'asc')
            ->limit($limit ?: config('hawk.etherscan.blocks.per_page'));
    }

    /**
     * Fetches ERC20 transactions for current wallet address and performs
     * API-level pagination if transaction block number is passed in `start`
     * parameter.
     *
     * @param ?int $start
     *
     * @return Collection<mixed>|null
     */
    private function getTransactionsFromAPI(?int $start = null): null|Collection
    {
        // Throw an exception if invalid wallet ID was provided
        if (!$this->wallet || !preg_match('/^0x[a-fA-F0-9]{40}$/', $this->wallet))
            throw new InvalidArgumentException('Invalid wallet ID provided!');

        // Fetch wallet transactions from API
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get('https://api.etherscan.io/api', [
                'module'     => 'account',
                'action'     => 'tokentx',
                'address'    => $this->wallet,
                'offset'     => 10000,
                'startblock' => $start,
                'apikey'     => config('hawk.etherscan.api_key')
            ]);

        // Throw new 500 error if API sent error or unexpected response
        if ($response->serverError())
            throw new InternalErrorException('Could not fetch transactions!');

        return collect($response->json()['result']);
    }


    /**
     * Parses and formats raw ERC20 transactions (fetched from Etherscan API)
     * and converts them into formatted `\App\Models\Etherscan` model
     * compatible schema array.
     *
     * @param array<mixed> $transaction
     *
     * @return array<mixed>
     */
    private function parseTransaction(array $transaction): array
    {
        return [
            // Recipients
            'accounts' => [
                'from' => $transaction['from'],
                'to'   => $transaction['from'],
            ],

            // Block details
            'block_timestamp' => (int) $transaction['timeStamp'],
            'block_number'    => (int) $transaction['blockNumber'],

            // Pricing (GAS)
            'gas' => [
                'cumulativeUsage' => $transaction['cumulativeGasUsed'],
                'gas'             => $transaction['gas'],
                'price'           => $transaction['gasPrice'],
                'usd'             => $transaction['gasUsed'],
            ],

            // Additional details
            'hash'  => $transaction['hash'],
            'confirmations' => (int) $transaction['confirmations'],
            'input' => $transaction['input'],
            'nonce' => (int) $transaction['nonce'],
            'value' => (int) $transaction['value'],

            // Asset token
            'token' => [
                'name'     => $transaction['tokenName'],
                'decimals' => $transaction['tokenDecimal'],
                'symbol'   => $transaction['tokenSymbol'],
            ],
        ];
    }

    /**
     * Saves passed formatted ERC20 transactions into database neglecting
     * already existing records. Returns a collection containing unique records
     * count, existing records count and collection of all passed transactions
     * eloquent models fetched/saved in database.
     *
     * @param \Illuminate\Support\Collection<array> $transactions
     *
     * @return \Illuminate\Support\Collection<mixed>
     */
    private function saveTransactions(Collection $transactions): Collection
    {
        // Check if any of passed transactions already exist in database or not
        $existing_transactions = Etherscan::whereIn(
            'hash',
            $transactions
                ->map(fn ($transaction) => $transaction['hash'])
                ->toArray()
        )
            ->get();

        // If no record exists then save and return them
        if (!$existing_transactions || $existing_transactions->count() === 0)
            return new Collection([
                'uniques'      => count($transactions),
                'existing'     => 0,
                'transactions' => $transactions->unique('hash')->map(function (array $transaction) {
                    return Etherscan::create($transaction);
                }),
            ]);

        // Grab transaction hash from existing records (for filtering)
        $existing_hashes = $existing_transactions
            ->map(fn ($transaction) => $transaction['hash']);

        $uniques = $transactions
            ->filter(fn ($transaction) => $existing_hashes->contains($transaction['hash']));

        // All records already exist in database, return those
        if (!$uniques || $uniques->empty()) return new Collection([
            'transactions' => $existing_transactions,
            'existing'     => $existing_transactions->count(),
            'uniques'      => 0,
        ]);

        // No record exists locally and all passed records are unique
        return new Collection([
            'transactions' => array_merge($existing_transactions, Etherscan::create($uniques->unique('hash')->toArray())),
            'existing'     => 0,
            'uniques'      => $existing_transactions->count(),
        ]);
    }
}
