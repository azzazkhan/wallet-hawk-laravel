<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Etherscan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class ExportEtherscan implements FromQuery
{
    use Exportable;

    public function __construct(protected array $model_ids)
    {
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return Etherscan::query()->find($this->model_ids);
    }

    /**
     * @var Etherscan $transaction
     */
    // public function map(Etherscan $transaction): array
    // {
    //     return [
    //         $transaction->token['name'],
    //         $transaction->direction,
    //         $this->calculateQuantity(
    //             $transaction->value,
    //             $transaction->token['decimals']
    //         ),
    //         $transaction->from,
    //         $transaction->to,
    //         number_format($this->gweiToEth($transaction->gas['price']), 3),
    //         (new Carbon($transaction->block_timestamp))->format('D jS M Y \a\t g:i:s A')
    //     ];
    // }

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
}
