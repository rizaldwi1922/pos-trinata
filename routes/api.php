<?php

use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TransactionHistoryController;

Route::group(['prefix' => 'v1', 'as' => 'v1.'], function () {
    Route::group(['middleware' => ['auth.api']], function () {
        Route::get('products', [APIController::class, 'getProducts']);
        Route::get('product-barcode', [APIController::class, 'getProductByBarcode']);
        Route::get('transactions', [APIController::class, 'getTransactions']);
        Route::get('checkout-data', [APIController::class, 'getCheckoutData']);
        Route::post('checkout', [APIController::class, 'postCheckout']);
        Route::post('login', [APIController::class, 'postLogin']);
        Route::post('pos-start-shift', [APIController::class, 'postPOSStartShift']);
        Route::post('pos-end-shift', [APIController::class, 'postPOSEndShift']);
    });
    Route::post('user-register', [APIController::class, 'postUserRegister']);
});

Route::post('/login', [AuthController::class, 'login']);

// POS (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Products & Categories
    Route::get('/categories', [PosController::class, 'getCategories']);
    Route::get('/products', [PosController::class, 'getProducts']);
    Route::get('/payment-methods', [PosController::class, 'getPaymentMethods']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);

    // // Transaction
    Route::post('/transactions', [PosController::class, 'submitPayment']);
    // Route::get('/transactions', [PosController::class, 'getHistoryTransaction']);
    // Route::get('/transactions/search', [PosController::class, 'searchTransaction']);

    // Shift
    Route::get('/shift/current', [PosController::class, 'getCurrentShift']);
    Route::post('/shift/start', [PosController::class, 'startShift']);
    Route::post('/shift/end', [PosController::class, 'endShift']);

    Route::get('/transactions', [TransactionHistoryController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionHistoryController::class, 'show']);
});
