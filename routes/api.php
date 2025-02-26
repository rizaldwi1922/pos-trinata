<?php

use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'v1.'], function () {
    Route::group(['middleware' => ['auth.api']], function() {
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
