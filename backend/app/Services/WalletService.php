<?php

namespace App\Services;

use App\Exceptions\DuplicateResourceException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function createWallet(array $data): array
    {
        $user = User::find($data['userId']);

        if (!$user) {
            throw new ResourceNotFoundException("User not found with id: {$data['userId']}");
        }

        if (Wallet::where('user_id', $data['userId'])->exists()) {
            throw new DuplicateResourceException("Wallet already exists for user id: {$data['userId']}");
        }

        $wallet = Wallet::create([
            'user_id' => $data['userId'],
            'balance' => $data['initialBalance'],
        ]);

        Log::info("Created wallet id={$wallet->id} for user id={$user->id} with balance={$wallet->balance}");

        return $this->toResponse($wallet);
    }

    public function getWalletById(int $walletId): array
    {
        $wallet = Wallet::with('user')->find($walletId);

        if (!$wallet) {
            throw new ResourceNotFoundException("Wallet not found with id: {$walletId}");
        }

        return $this->toResponse($wallet);
    }

    public function deposit(int $walletId, float $amount): array
    {
        return DB::transaction(function () use ($walletId, $amount) {
            $wallet = Wallet::lockForUpdate()->find($walletId);

            if (!$wallet) {
                throw new ResourceNotFoundException("Wallet not found with id: {$walletId}");
            }

            $wallet->balance = bcadd($wallet->balance, $amount, 4);
            $wallet->version = $wallet->version + 1;
            $wallet->save();

            Transaction::create([
                'from_wallet_id' => null,
                'to_wallet_id' => $walletId,
                'amount' => $amount,
                'status' => 'DEPOSIT',
            ]);

            Log::info("Deposited {$amount} to wallet id={$walletId}, new balance={$wallet->balance}");

            return $this->toResponse($wallet);
        });
    }

    public function toResponse(Wallet $wallet): array
    {
        $wallet->loadMissing('user');

        return [
            'id' => $wallet->id,
            'userId' => $wallet->user_id,
            'userEmail' => $wallet->user->email,
            'balance' => $wallet->balance,
            'createdAt' => $wallet->created_at?->toISOString(),
            'updatedAt' => $wallet->updated_at?->toISOString(),
        ];
    }
}
