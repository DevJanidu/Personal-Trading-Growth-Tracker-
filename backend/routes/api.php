<?php

use App\Http\Controllers\Api\V1\AccountTransactionController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\StrategyController;
use App\Http\Controllers\Api\V1\StrategySetupController;
use App\Http\Controllers\Api\V1\Taxonomy\EntryModelController;
use App\Http\Controllers\Api\V1\Taxonomy\MarketConditionController;
use App\Http\Controllers\Api\V1\Taxonomy\SetupGradeController;
use App\Http\Controllers\Api\V1\Taxonomy\TradingSessionController;
use App\Http\Controllers\Api\V1\TradingAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('accounts', TradingAccountController::class);

        Route::get('accounts/{account}/transactions', [AccountTransactionController::class, 'index']);
        Route::post('accounts/{account}/transactions', [AccountTransactionController::class, 'store']);

        Route::apiResource('strategies', StrategyController::class);
        Route::apiResource('strategies.setups', StrategySetupController::class)->except(['show']);

        Route::apiResource('trading-sessions', TradingSessionController::class)->except(['show']);
        Route::apiResource('market-conditions', MarketConditionController::class)->except(['show']);
        Route::apiResource('entry-models', EntryModelController::class)->except(['show']);
        Route::apiResource('setup-grades', SetupGradeController::class)->except(['show']);
    });
});
