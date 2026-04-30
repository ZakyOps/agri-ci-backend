<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RepaymentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Agri CI opérationnelle.',
        'base_url' => rtrim((string) config('app.url'), '/').'/api',
        'comptes_demo' => [
            'admin' => 'admin@agrici.ci / password',
            'superviseur' => 'supervisor.abidjan@agrici.ci / password',
            'operateur' => 'operator.abidjan@agrici.ci / password',
        ],
        'routes_test' => [
            'POST /api/auth/login',
            'GET /api/categories',
            'GET /api/products',
            'GET /api/farmers/search?query=FCI-0001',
        ],
        'note' => 'Les autres routes protégées demandent le header Authorization: Bearer TOKEN.',
    ]);
});

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/supervisors', [UserController::class, 'supervisors']);
        Route::post('/supervisors', [UserController::class, 'storeSupervisor']);
        Route::get('/supervisors/{user}', [UserController::class, 'show']);
        Route::put('/supervisors/{user}', [UserController::class, 'update']);
        Route::delete('/supervisors/{user}', [UserController::class, 'destroy']);
    });

    Route::middleware('role:supervisor')->group(function () {
        Route::get('/operators', [UserController::class, 'operators']);
        Route::post('/operators', [UserController::class, 'storeOperator']);
        Route::get('/operators/{user}', [UserController::class, 'show']);
        Route::put('/operators/{user}', [UserController::class, 'update']);
        Route::delete('/operators/{user}', [UserController::class, 'destroy']);
    });

    Route::middleware('role:admin,supervisor')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings/{key}', [SettingController::class, 'update']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::get('/farmers/search', [FarmerController::class, 'search']);
    Route::get('/farmers/{farmer}/debts', [FarmerController::class, 'debts']);
    Route::get('/farmers/{farmer}/repayments', [RepaymentController::class, 'farmerRepayments']);
    Route::apiResource('farmers', FarmerController::class)->except(['destroy']);

    Route::middleware('role:operator')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::post('/repayments', [RepaymentController::class, 'store']);
    });
});
