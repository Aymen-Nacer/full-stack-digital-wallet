<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $response = $this->userService->createUser($request->validated());
        return response()->json($response, 201);
    }

    public function index(): JsonResponse
    {
        return response()->json($this->userService->getAllUsers());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->userService->getUserById($id));
    }
}
