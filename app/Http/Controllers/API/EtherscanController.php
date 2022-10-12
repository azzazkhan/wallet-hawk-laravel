<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\HandlesEtherscanTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EtherscanController extends Controller
{
    use HandlesEtherscanTransactions;

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
