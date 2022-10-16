<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\HandlesEtherscanTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class EtherscanController extends Controller
{
    use HandlesEtherscanTransactions;

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'address' => ['required', 'string', 'min:40', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'page'    => ['nullable', 'numeric', 'min:2']
        ]);

        $wallet = Str::lower($request->input('address'));
        $page   = $request->input('page', 1);

        $transactions = static::fetch_transactions($wallet, $page);
        $transactions = collect($transactions)
            ->map(fn ($record) => static::parse_transaction($wallet, $record));

        return response()
            ->json([
                'success' => true,
                'status'  => Response::HTTP_OK,
                'data'    => $transactions,
            ]);
    }
}
