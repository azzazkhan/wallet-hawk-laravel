<?php

namespace App\Http\Controllers;

use App\Helpers\JSON;

class TransactionsController extends Controller
{
    public function index()
    {
        return view('transactions', [
            'transactions' => JSON::parseFile('erc20.json')['transactions']
        ]);
    }
}
