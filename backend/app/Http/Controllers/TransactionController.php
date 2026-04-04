<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 20);
        return response()->json($this->transactionService->getRecentTransactions($limit));
    }
}
