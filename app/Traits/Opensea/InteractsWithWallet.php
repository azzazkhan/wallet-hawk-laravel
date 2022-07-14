<?php

namespace App\Traits\Opensea;

use App\Models\Wallet;

trait InteractsWithWallet
{
    private function hasCooledDown(): bool
    {
    }
    private function updateLockout(): void
    {
    }

    private function hasPaginationCooledDown(): bool
    {
    }
    private function updatePaginationLockout(): void
    {
    }

    private function walletExists(string $wallet): bool
    {
        return $this->getWallet($wallet) ? true : false;
    }

    private function createWallet(string $wallet): Wallet
    {
        $wallet = $this->getWallet($wallet);

        return $wallet ?: Wallet::create(['wallet_id' => $wallet]);
    }

    private function getWallet(string $wallet): Wallet|null
    {
        return Wallet::where('wallet_id', $wallet)->first();
    }
}
