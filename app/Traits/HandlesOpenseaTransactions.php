<?php

namespace App\Traits;

use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

trait HandlesOpenseaTransactions
{
    private function fetchEvents(string $account_address, string $event_type = null): array
    {
        // Fetch the wallet document
        $wallet = Wallet::where('wallet_id', $account_address)->first();

        if ($wallet) {
            // Check if we have already fetched all transactions for this wallet or not
            if ($wallet->opensea_index) {
                Wallet::getForWallet($account_address);
            }
        }


        // Wallet details does not exist in our records, this means the
        // transactions for this wallet are being searched for first time
        if (!$wallet)
            // Create new record against requested wallet address
            $wallet = Wallet::create(['wallet_id' => $account_address]);


        // Fetch transactions for requested wallet address
        $response = Http::retry(3, 300)
            ->acceptJson()
            ->withHeaders([
                "X-API-KEY" => env('OPENSEA_API_KEY')
            ])
            ->get('https://api.opensea.io/api/v1/events', compact('account_address', 'event_type'));

        return $response->json();
    }

    private function parseEvent(array $event): array
    {
        $asset = $event['asset'];
        $contract = $event['asset']['asset_contract'];
        $payment_token = $event['payment_token'];

        return [
            'schema'     => strtoupper($contract['schema_name']),
            'event_type' => strtolower($event['event_type']),
            'asset_id'   => (int) $asset['id'],

            'media'           => [
                'image' => [
                    'url'       => $asset['image_url'] ?: null,
                    'original'  => $asset['image_original_url'] ?: null,
                    'preview'   => $asset['image_preview_url'] ?: null,
                    'thumbnail' => $asset['image_thumbnail_url'] ?: null,
                ],
                'animation' => [
                    'url'       => $asset['animation_url'] ?: null,
                    'original'  => $asset['animation_original_url'] ?: null,
                ],
            ],
            'asset'           => [
                'name'          => $asset['name'],
                'description'   => $asset['description'] ?: null,
                'external_link' => $asset['external_link'] ?: null,
            ],
            'payment_token'   => $payment_token ? [
                'decimals' => (int) $payment_token['decimals'],
                'symbol'   => $payment_token['symbol'],
                'eth'      => (float) $payment_token['eth'],
                'usd'      => (float) $payment_token['usd'],
            ] : null,
            'contract'        => [
                'address' => $contract['address'],
                'type'    => $contract['asset_contract_type'],
                'date'    => (int) (new Carbon($contract['created_date']))->format('U'),
            ],
            'accounts'        => [
                'from'   => $event['from_account'] ? ($event['from_account']['address'] ?: null) : null,
                'to'     => $event['to_account'] ? ($event['to_account']['address'] ?: null) : null,
                'seller' => $event['seller'] ? ($event['seller']['address'] ?: null) : null,
                'winner' => $event['winner_account'] ? ($event['winner_account']['address'] ?: null) : null,
            ],

            'event_id'        => $event['event_id'],
            'event_timestamp' => (int) (new Carbon($event['event_timestamp']))->format('U'),
        ];
    }
    private function updateLockoutTimer(Wallet $wallet)
    {
    }
    private function updatePaginationTimer(Wallet $wallet)
    {
    }
}
