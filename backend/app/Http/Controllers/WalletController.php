<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWalletRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    ) {}

    public function store(CreateWalletRequest $request): JsonResponse
    {
        $response = $this->walletService->createWallet($request->validated());
        return response()->json($response, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->walletService->getWalletById($id));
    }

    public function deposit(Request $request, int $id): JsonResponse
    {
        $amount = $request->query('amount');

        if (!$amount || (float) $amount <= 0) {
            throw new \InvalidArgumentException('Deposit amount must be positive');
        }

        $response = $this->walletService->deposit($id, (float) $amount);
        return response()->json($response);
    }
}
