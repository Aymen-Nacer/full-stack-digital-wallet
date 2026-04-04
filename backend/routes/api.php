<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Users
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);

// Wallets
Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets/{id}', [WalletController::class, 'show']);
Route::post('/wallets/{id}/deposit', [WalletController::class, 'deposit']);

// Transfer
Route::post('/transfer', [TransferController::class, 'store']);

// Transactions
Route::get('/transactions', [TransactionController::class, 'index']);
