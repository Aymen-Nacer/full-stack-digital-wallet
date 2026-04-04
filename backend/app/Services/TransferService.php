<?php

namespace App\Services;

use App\Exceptions\IdempotencyConflictException;
use App\Exceptions\InvalidTransferException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    /**
     * Transfers money between two wallets atomically.
     *
     * Race condition prevention strategy:
     *   - Pessimistic locking (SELECT FOR UPDATE) on both wallets.
     *   - Wallets are always locked in ascending ID order to prevent deadlocks.
     *
     * Idempotency:
     *   - If an idempotencyKey is provided and already exists, return the existing result.
     */
    public function transfer(array $data): array
    {
        $this->validateRequest($data);

        if (!empty($data['idempotencyKey'])) {
            return $this->handleWithIdempotency($data);
        }

        return $this->executeTransfer($data);
    }

    private function handleWithIdempotency(array $data): array
    {
        $existing = Transaction::where('idempotency_key', $data['idempotencyKey'])->first();

        if ($existing) {
            if ($existing->from_wallet_id != $data['fromWalletId']
                || $existing->to_wallet_id != $data['toWalletId']
                || bccomp($existing->amount, $data['amount'], 4) !== 0) {
                throw new IdempotencyConflictException(
                    "Idempotency key '{$data['idempotencyKey']}' was already used for a different transfer"
                );
            }

            Log::info("Idempotency hit for key={$data['idempotencyKey']}, returning existing transaction id={$existing->id}");
            return $this->toResponse($existing);
        }

        return $this->executeTransfer($data);
    }

    private function executeTransfer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            Log::info("Starting transfer: fromWallet={$data['fromWalletId']} toWallet={$data['toWalletId']} amount={$data['amount']}");

            // Lock wallets in consistent order (ascending ID) to prevent deadlocks
            $firstLockId = min($data['fromWalletId'], $data['toWalletId']);
            $secondLockId = max($data['fromWalletId'], $data['toWalletId']);

            $firstLocked = Wallet::lockForUpdate()->find($firstLockId);
            if (!$firstLocked) {
                throw new ResourceNotFoundException("Wallet not found with id: {$firstLockId}");
            }

            $secondLocked = Wallet::lockForUpdate()->find($secondLockId);
            if (!$secondLocked) {
                throw new ResourceNotFoundException("Wallet not found with id: {$secondLockId}");
            }

            $sourceWallet = ($firstLockId == $data['fromWalletId']) ? $firstLocked : $secondLocked;
            $targetWallet = ($firstLockId == $data['toWalletId']) ? $firstLocked : $secondLocked;

            // Validate sufficient balance
            if (bccomp($sourceWallet->balance, $data['amount'], 4) < 0) {
                Log::warning("Insufficient balance in wallet id={$sourceWallet->id}: available={$sourceWallet->balance}, requested={$data['amount']}");

                $failed = $this->recordFailedTransaction($data,
                    "Insufficient balance: available={$sourceWallet->balance}, requested={$data['amount']}");
                return $this->toResponse($failed);
            }

            // Debit source
            $sourceWallet->balance = bcsub($sourceWallet->balance, $data['amount'], 4);
            $sourceWallet->version = $sourceWallet->version + 1;
            $sourceWallet->save();

            // Credit target
            $targetWallet->balance = bcadd($targetWallet->balance, $data['amount'], 4);
            $targetWallet->version = $targetWallet->version + 1;
            $targetWallet->save();

            $success = $this->recordTransaction($data, 'SUCCESS', null);

            Log::info("Transfer completed: transactionId={$success->id} fromWallet={$data['fromWalletId']} toWallet={$data['toWalletId']} amount={$data['amount']}");

            return $this->toResponse($success);
        });
    }

    private function validateRequest(array $data): void
    {
        if ($data['fromWalletId'] == $data['toWalletId']) {
            throw new InvalidTransferException('Cannot transfer to the same wallet');
        }

        if (bccomp($data['amount'], '0', 4) <= 0) {
            throw new InvalidTransferException('Transfer amount must be positive');
        }
    }

    private function recordTransaction(array $data, string $status, ?string $failureReason): Transaction
    {
        return Transaction::create([
            'from_wallet_id' => $data['fromWalletId'],
            'to_wallet_id' => $data['toWalletId'],
            'amount' => $data['amount'],
            'status' => $status,
            'idempotency_key' => $data['idempotencyKey'] ?? null,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Records a FAILED transaction without storing the idempotency key,
     * so that retries with the same key can re-attempt the transfer.
     */
    private function recordFailedTransaction(array $data, string $failureReason): Transaction
    {
        return Transaction::create([
            'from_wallet_id' => $data['fromWalletId'],
            'to_wallet_id' => $data['toWalletId'],
            'amount' => $data['amount'],
            'status' => 'FAILED',
            'idempotency_key' => null,
            'failure_reason' => $failureReason,
        ]);
    }

    private function toResponse(Transaction $transaction): array
    {
        return [
            'transactionId' => $transaction->id,
            'fromWalletId' => $transaction->from_wallet_id,
            'toWalletId' => $transaction->to_wallet_id,
            'amount' => $transaction->amount,
            'status' => $transaction->status,
            'idempotencyKey' => $transaction->idempotency_key,
            'createdAt' => $transaction->created_at?->toISOString(),
        ];
    }
}
