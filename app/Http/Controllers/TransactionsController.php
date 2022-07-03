<?php

namespace App\Http\Controllers;

use App\Helpers\JSON;
use App\Traits\HandlesOpenseaTransactions;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    use HandlesOpenseaTransactions;

    public function index(Request $request)
    {
        $wallet_id = $request->validate([
            'wallet' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/']
        ])['wallet'];

        $transactions = $this->fetchEvents($wallet_id);

        dd($transactions);

        return view('transactions', [
            'transactions' => JSON::parseFile('erc20.json')['transactions']
        ]);
    }
}
