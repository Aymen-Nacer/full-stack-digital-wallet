<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(
        private readonly TransferService $transferService
    ) {}

    public function store(TransferRequest $request): JsonResponse
    {
        $response = $this->transferService->transfer($request->validated());
        return response()->json($response);
    }
}
