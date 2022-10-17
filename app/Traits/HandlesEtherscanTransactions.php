<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait HandlesEtherscanTransactions
{
    private static function fetch_transactions(string $wallet, int $page = 1): array
    {
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get('https://api.etherscan.io/api', [
                'module'  => 'account',
                'action'  => 'tokentx',
                'address' => $wallet,
                'offset'  => 100,
                'page'    => $page,
                'sort'    => 'desc',
                'apikey'  => config('hawk.etherscan.api_key')
            ]);

        return $response->json('result', []);
    }

    private static function parse_transaction(string $wallet, array $transaction): array
    {
        $value     = $transaction['value'];
        $decimals  = $transaction['tokenDecimal'];
        $quantity  = $value && $decimals ? round($value / (pow(10, $decimals)), 3) : 0;

        $gas_price = $transaction['gasPrice'];
        $fee       = $gas_price ? round($gas_price / 1000000000, 3) : null;

        $from      = Str::lower($transaction['from']);
        $to        = Str::lower($transaction['to']);
        $direction = $wallet === $from ? 'out' : ($wallet === $to ? 'in' : null);
        $timestamp = ((int) (new Carbon((int) $transaction['timeStamp']))->format('U'));
        $hash      = $transaction['hash'];
        $name      = $transaction['tokenName'];

        return compact('hash', 'name', 'direction', 'quantity', 'from', 'to', 'fee', 'timestamp');
    }
}
