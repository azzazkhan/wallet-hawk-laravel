<?php

namespace App\Http\Livewire\Transactions\Tables;

use App\Models\ERC20;
use App\Models\Wallet;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use InvalidArgumentException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class Etherscan extends Component
{
    private const PER_PAGE = 20; // Records per page

    public string $wallet; // Wallet address
    public EloquentCollection $transactions; // Fetched transactions

    // Query string synchronized with internal component state
    protected array $queryString = ['wallet'];

    public function render()
    {
        return view('livewire.transactions.tables.etherscan');
    }

    public function loadTransactions(): void
    {
        // First load transactions from database
        $records = $this->getTransactionsQuery()->get();

        // If no transactions exists against this wallet then load from API and
        // cache them locally
        if ($records->empty() || $records->count() == 0) {
            $records = $this->processTransactions(
                $this->getTransactionsFromAPI()
            );

            $this->transactions = $records->get('transactions');
            return;
        }

        $this->transactions = $records;
    }

    public function loadMoreTransactions()
    {
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
    private function getTransactionsQuery(?int $limit = self::PER_PAGE): EloquentBuilder
    {
        return ERC20::query()
            ->where(function (EloquentBuilder $query) {
                return $query
                    ->where('accounts->from', $this->wallet)
                    ->orWhere('accounts->to', $this->wallet);
            })
            ->limit($limit);
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
     * and converts them into formatted `\App\Models\ERC20` model compatible
     * schema array.
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
            'block_timestamp' => (int) (new Carbon($transaction['timeStamp']))->format('U'),
            'block_number'    => (int) $transaction['blockNumber'],
            'block_hash'      => $transaction['hash'],
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
            'token' => [
                'name'     => $transaction['tokenName'],
                'decimals' => $transaction['tokenDecimal'],
                'symbol'   => $transaction['tokenSymbol'],
            ],
            'value' => (int) $transaction['value'],
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
        $existing_transactions = ERC20::whereIn(
            'block_hash',
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
                'transactions' => $transactions->map(fn ($transaction) => ERC20::create($transaction)),
            ]);

        // Grab transaction hash from existing records (for filtering)
        $existing_hashes = $existing_transactions
            ->map(fn ($transaction) => $transaction['hash']);

        $uniques = $transactions
            ->filter(fn ($transaction) => $existing_hashes->contains($transaction['hash']));

        // All records already exist in database, return those
        if (!$uniques || $uniques->empty()) return new Collection([
            'events'   => $existing_transactions,
            'existing' => $existing_transactions->count(),
            'uniques'  => 0,
        ]);

        // No record exists locally and all passed records are unique
        return new Collection([
            'events'   => array_merge($existing_transactions, ERC20::create($uniques)),
            'existing' => 0,
            'uniques'  => $existing_transactions->count(),
        ]);
    }
}
