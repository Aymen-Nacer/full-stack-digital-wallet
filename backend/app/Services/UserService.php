<?php

namespace App\Services;

use App\Exceptions\DuplicateResourceException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function createUser(array $data): array
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new DuplicateResourceException("User with email '{$data['email']}' already exists");
        }

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'full_name' => $data['fullName'],
            ]);

            Log::info("Created user id={$user->id} email={$user->email}");

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            Log::info("Created wallet id={$wallet->id} for user id={$user->id}");

            return $this->toResponse($user, $wallet->id);
        });
    }

    public function getAllUsers(): array
    {
        return User::all()->map(function (User $user) {
            $wallet = Wallet::where('user_id', $user->id)->first();
            return $this->toResponse($user, $wallet?->id);
        })->toArray();
    }

    public function getUserById(int $userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new ResourceNotFoundException("User not found with id: {$userId}");
        }

        $wallet = Wallet::where('user_id', $userId)->first();
        return $this->toResponse($user, $wallet?->id);
    }

    private function toResponse(User $user, ?int $walletId): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'fullName' => $user->full_name,
            'walletId' => $walletId,
            'createdAt' => $user->created_at?->toISOString(),
        ];
    }
}
