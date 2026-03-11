<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\GatewaysController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UsersController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/transactions', [TransactionsController::class, 'buy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UsersController::class, 'index']);
    Route::post('/users', [UsersController::class, 'store']);
    Route::get('/users/{user}', [UsersController::class, 'show']);
    Route::patch('/users/{user}', [UsersController::class, 'update']);
    Route::delete('/users/{user}', [UsersController::class, 'destroy']);

    Route::get('/products', [ProductsController::class, 'index']);
    Route::post('/products', [ProductsController::class, 'store']);
    Route::get('/products/{product}', [ProductsController::class, 'show']);
    Route::patch('/products/{product}', [ProductsController::class, 'update']);
    Route::delete('/products/{product}', [ProductsController::class, 'destroy']);

    Route::get('/clients', [ClientsController::class, 'index']);
    Route::get('/clients/{client}', [ClientsController::class, 'show']);
    Route::get('/clients/{client}/details', [ClientsController::class, 'details']);
    Route::get('/clients/{client}/purchases', [ClientsController::class, 'purchases']);
    Route::get('/clients/purchases/{transaction}', [ClientsController::class, 'detailPurchased']);
    Route::post('/clients/purchases/{transaction}/refund', [ClientsController::class, 'refundPuchasedGateway']);

    Route::get('/transactions', [TransactionsController::class, 'index']);
    Route::get('/transactions/{transaction}', [TransactionsController::class, 'show']);
    Route::post('/transactions/{transaction}/refund', [TransactionsController::class, 'refund']);

    Route::patch('/gateways/{gateway}/activate', [GatewaysController::class, 'activate']);
    Route::patch('/gateways/{gateway}/deactivate', [GatewaysController::class, 'deactivate']);
    Route::patch('/gateways/{gateway}/priority', [GatewaysController::class, 'priority']);
});
