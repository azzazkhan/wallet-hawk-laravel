<?php

namespace App\Http\Livewire\Transactions\Tables;

use App\Models\ERC20;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Etherscan extends Component
{
    private const PER_PAGE = 20; // Records per page

    public string $wallet;
    public EloquentCollection $transactions;

    public function mount(string $wallet): void
    {
        $this->wallet = $wallet;
        $this->getTransactions();
    }

    public function render()
    {
        return view('livewire.transactions.tables.etherscan');
    }

    private function getTransactions()
    {
        $transactions = $this->saveTransactions($this->getTransactionsFromAPI());
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
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get('https://api.etherscan.io/api', [
                'address' => $this->wallet,
                'offset'  => 10000,
                'startblock' => $start,
                'apikey'  => config('hawk.etherscan.api_key')
            ]);

        return $response->serverError() ? null : collect($response->json()['result']);
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
     * already existing records. Returns an array containing unique records
     * count, existing records count and collection of all passed transactions
     * eloquent models fetched/saved in database.
     *
     * @param \Illuminate\Support\Collection<array> $transactions
     *
     * @return array
     */
    private function saveTransactions(Collection $transactions): array
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
            return [
                'uniques'      => count($transactions),
                'existing'     => 0,
                'transactions' => $transactions->map(fn ($transaction) => ERC20::create($transaction)),
            ];

        // Grab transaction hash from existing records (for filtering)
        $existing_hashes = $existing_transactions
            ->map(fn ($transaction) => $transaction['hash']);

        $uniques = $transactions
            ->filter(fn ($transaction) => $existing_hashes->contains($transaction['hash']));

        // All records already exist in database, return those
        if (!$uniques || $uniques->empty()) return [
            'events'   => $existing_transactions,
            'existing' => $existing_transactions->count(),
            'uniques'  => 0,
        ];

        // No record exists locally and all passed records are unique
        return [
            'events'   => array_merge($existing_transactions, ERC20::create($uniques)),
            'existing' => 0,
            'uniques'  => $existing_transactions->count(),
        ];
    }
}
