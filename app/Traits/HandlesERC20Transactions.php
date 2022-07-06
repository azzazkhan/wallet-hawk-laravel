<?php

namespace App\Traits;

use CStr;
use Illuminate\Support\Facades\Http;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

trait HandlesERC20Transactions
{
    // Write your code here

    private function fetchFromEtherscanAPI(
        string $wallet_id,
        string $limit,
        ?int $page = 1,
    ): array {
        if (!$this->incrementEtherscanCounter())
            throw new TooManyRequestsHttpException(
                2, // Retry after (seconds)
                'Server overloaded, please try again in few moments',
            );

        // Keep track of how many calls we have consumed so far
        $this->incrementEtherscanAPICallCounter();

        $response = Http::retry(3, 300)
            ->acceptJson()
            ->get('https://api.etherscan.io/api', [
                'address' => $wallet_id,
                'page'    => $page,
                'offset'  => $limit,
                'apikey'  => config('hawk.etherscan.api_key')
            ]);

        $this->decrementEtherscanCounter();

        if ($response->serverError())
            throw new InternalErrorException('The records service is having issues!');

        $data = $response?->json();

        return is_array($data['result']) ? $data['result'] : [];
    }

    private function parseEtherscanEvent(): array
    {
        return [];
    }
    private function hasEtherscanCooledDown(): bool
    {
        return true;
    }
    private function updateEtherscanLockoutTimer(): void
    {
    }
    private function hasEtherscanPaginationTimerExhausted(): bool
    {
        return true;
    }
    private function updateEtherscanPaginationTimer(): void
    {
    }
    private function hasEtherscanIndexed(): bool
    {
        return true;
    }
    private function setEtherscanIndexed(): void
    {
    }
    private function incrementEtherscanCounter(): bool
    {
        return true;
    }
    private function decrementEtherscanCounter(): void
    {
    }
    private function canIncrementEtherscanCounter(): bool
    {
        return true;
    }
    private function incrementEtherscanAPICallCounter(): void
    {
    }
}
