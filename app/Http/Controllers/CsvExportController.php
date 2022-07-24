<?php

namespace App\Http\Controllers;

use App\Models\Etherscan;
use App\Models\Opensea;
use Illuminate\Http\Request;

class CsvExportController extends Controller
{
    public function etherscan(Request $request)
    {
        function calculateQuantity(int $value, ?int $decimals = 0): float
        {
            if (!$value || !$decimals) return 0;

            return round($value / (pow(10, $decimals)), 3);
        }

        function gweiToEth(int $gwei): float
        {
            return $gwei / 1000000000;
        }

        $request->validate([
            'ids' => ['required', 'string']
        ]);

        $transactions = Etherscan::find(explode(',', $request->query('ids')));

        $rows = collect([]);
        $rows = $rows->push(['Item', 'In/OUT', 'Quantity', 'From', 'To', 'Txn FEE', 'Timestamp']);

        foreach ($transactions as $transaction)
            $rows = $rows->push([
                $transaction->token['name'],
                $transaction->direction,
                calculateQuantity($transaction->value, $transaction->token['decimals']),
                $transaction->accounts['from'],
                $transaction->accounts['to'],
                round(gweiToEth($transaction->gas['price']), 3),
                (new \Illuminate\Support\Carbon($transaction->block_timestamp))->format('D jS M Y \a\t G:i:s A \G\M\T')
            ]);

        $content = $rows
            ->map(function (array $row) {
                return collect($row)
                    ->map(fn ($value) => sprintf('"%s"', $value))
                    ->join(",");
            })
            ->join("\n");

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'transactions.csv', ['Content-Type' => 'text/csv']);
    }

    public function opensea(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'string']
        ]);

        $events = Opensea::find(explode(',', $request->query('ids')));

        $rows = collect([]);
        $rows = $rows->push(['Item', 'In/OUT', 'From', 'To', 'Type', 'Event Type', 'Value', 'Timestamp']);

        foreach ($events as $event)
            $rows = $rows->push([
                $event->asset['name'],
                $event->direction,
                is_array($event->accounts['from'])
                    ? $event->accounts['from']['address']
                    : (is_array($event->accounts['seller'])
                        ? $event->accounts['seller']['address']
                        : '--'
                    ),
                is_array($event->accounts['to'])
                    ? $event->accounts['to']['address']
                    : (is_array($event->accounts['winner'])
                        ? $event->accounts['winner']['address']
                        : '--'
                    ),
                strtoupper($event->schema),
                ucfirst(preg_replace('/(successful)/', 'sale', strtolower($event->event_type))),
                $event->payment_token && is_array($event->payment_token) ? sprintf(
                    '%s ETH, %s USD',
                    number_format((int) $event->payment_token['eth'], 4),
                    number_format(round((int) $event->payment_token['usd']), 0)
                ) : '--',
                (new \Illuminate\Support\Carbon($event->block_timestamp))->format('D jS M Y \a\t G:i:s A \G\M\T')
            ]);

        $content = $rows
            ->map(function (array $row) {
                return collect($row)
                    ->map(fn ($value) => sprintf('"%s"', $value))
                    ->join(",");
            })
            ->join("\n");

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'transactions.csv', ['Content-Type' => 'text/csv']);
    }
}
