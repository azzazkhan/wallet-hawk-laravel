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
        $wallet_id = $request->validate([
            'wallet' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'event'  => ['string', Rule::in(config('hawk.opensea.event.types'))]
        ])['wallet'];

        dd($this->fetchEvents($wallet_id));

        return view('transactions', [
            'transactions' => JSON::parseFile('erc20.json')['transactions']
        ]);
    }
}
