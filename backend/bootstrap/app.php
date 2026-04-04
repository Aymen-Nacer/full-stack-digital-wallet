<?php

use App\Exceptions\DuplicateResourceException;
use App\Exceptions\IdempotencyConflictException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidTransferException;
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (ResourceNotFoundException $e, Request $request) {
            return response()->json([
                'status' => 404,
                'error' => 'Not Found',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 404);
        });

        $exceptions->renderable(function (InsufficientBalanceException $e, Request $request) {
            return response()->json([
                'status' => 422,
                'error' => 'Insufficient Balance',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 422);
        });

        $exceptions->renderable(function (DuplicateResourceException $e, Request $request) {
            return response()->json([
                'status' => 409,
                'error' => 'Conflict',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 409);
        });

        $exceptions->renderable(function (InvalidTransferException $e, Request $request) {
            return response()->json([
                'status' => 400,
                'error' => 'Invalid Transfer',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 400);
        });

        $exceptions->renderable(function (IdempotencyConflictException $e, Request $request) {
            return response()->json([
                'status' => 409,
                'error' => 'Idempotency Conflict',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 409);
        });

        $exceptions->renderable(function (\InvalidArgumentException $e, Request $request) {
            return response()->json([
                'status' => 400,
                'error' => 'Bad Request',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 400);
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            $details = collect($e->errors())->flatten()->values()->all();
            return response()->json([
                'status' => 400,
                'error' => 'Validation Failed',
                'message' => 'Input validation failed',
                'details' => $details,
                'timestamp' => now()->toISOString(),
            ], 400);
        });
    })->create();
