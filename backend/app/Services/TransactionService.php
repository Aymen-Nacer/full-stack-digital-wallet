<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    public function getRecentTransactions(int $limit = 20): array
    {
        return Transaction::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (Transaction $t) {
                return [
                    'transactionId' => $t->id,
                    'fromWalletId' => $t->from_wallet_id,
                    'toWalletId' => $t->to_wallet_id,
                    'amount' => $t->amount,
                    'status' => $t->status,
                    'idempotencyKey' => $t->idempotency_key,
                    'createdAt' => $t->created_at?->toISOString(),
                ];
            })
            ->toArray();
    }
}
