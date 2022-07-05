<?php

namespace App\Http\Controllers;

use App\Helpers\JSON;
use App\Traits\HandlesOpenseaTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'wallet' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'event'  => ['string', Rule::in(config('hawk.opensea.event.types'))],
            // Previews records
            'before' => ['numeric'],
            'previous' => ['string', 'min:6', 'regex:/(([A-z0-9])=?){60,}$/'],
            // Newer records
            'next' => ['string', 'min:6', 'regex:/(([A-z0-9])=?){60,}$/'],
            'after' => ['string', 'min:6', 'regex:/(([A-z0-9])=?){60,}$/'],
        ]);

        // Cursor navigation (API only)
        if ($request->has('previous')) {
            $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event'),
                cursor: $request->query('cursor')
            );
        }
        // Custom pagination (from database)
        else if ($request->has('before')) {
        }

        if ($request->has('next')) {
            $this->fetchFromOpenseaAPI(
                wallet_id: $request->query('wallet'),
                type: $request->query('event'),
                cursor: $request->query('next')
            );
        }

        // Previous records (previous, before)
        // Newer records (next, after)
    }
}
