<?php

namespace App\Http\Controllers;

use App\Models\Opensea;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        return view('transactions.list');
    }

    public function details(Request $request, string $address, string $event_id)
    {
        // Invalid wallet/event ID provided
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address) || !is_numeric($event_id))
            throw new NotFoundHttpException();

        $event = Opensea::query()
            ->where('wallet', $address)
            ->where('event_id', $event_id)
            ->firstOrFail();
        $wallet = $address;

        return view('transactions.details', compact('event', 'wallet'));
    }
}
