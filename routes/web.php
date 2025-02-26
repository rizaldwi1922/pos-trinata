<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DailyStockController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductUnitController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductIngredientController;
use App\Http\Controllers\Admin\ProductIngredientStockController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchasePaymentController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\IngredientStockController;
use App\Http\Controllers\Admin\ProductVariantStockController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Super\RoleController;
use App\Http\Controllers\Super\PermissionController;
use App\Http\Controllers\Super\StoreController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\Webmin\DashboardController as WebminDashboardController;
use App\Http\Controllers\Webmin\NewsController;
use App\Http\Controllers\Webmin\RegisterUserController;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'web.', 'middleware' => ['webmin']], function () {
    Route::get('/', [WebController::class, 'home']);
    Route::get('/home', [WebController::class, 'home'])->name('home');
    Route::get('features', [WebController::class, 'features'])->name('features');
    Route::get('pricing', [WebController::class, 'pricing'])->name('pricing');
    Route::get('news', [WebController::class, 'news'])->name('news');
    Route::get('news/{slug}', [WebController::class, 'newsDetail'])->name('news.detail');
    Route::get('register', [WebController::class, 'register'])->name('register');
    Route::post('register', [WebController::class, 'postRegister']);
});

// Auth
Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {
    Route::group(['middleware' => ['guest']], function () {
        Route::get('login', [AuthController::class, 'getLogin'])->name('login');
        Route::post('login', [AuthController::class, 'postLogin']);
    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('logout', [AuthController::class, 'getLogout'])->name('logout');
    });
});

// Super Admin
Route::group(['middleware' => ['auth', 'role:super-admin'], 'as' => 'super.', 'prefix' => 'super'], function () {
    Route::get('login_as/{store}', [AuthController::class, 'getLoginAs'])->name('login_as');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::resource('stores', StoreController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
});

// Admin
Route::group(['as' => 'admin.', 'prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth']], function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');

        Route::group(['as' => 'reports.', 'prefix' => 'report'], function () {
            Route::get('transactions', [ReportController::class, 'indexTransaction'])->name('transactions.index');
            Route::get('transactions/show', [ReportController::class, 'showTransaction'])->name('transactions.show');
            Route::get('transactions/download', [ReportController::class, 'exportTransaction'])->name('transactions.download');

            Route::get('purchases', [ReportController::class, 'indexPurchase'])->name('purchases.index');
            Route::get('purchases/show', [ReportController::class, 'showPurchase'])->name('purchases.show');
            Route::get('purchases/download', [ReportController::class, 'exportPurchase'])->name('purchases.download');
        });

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::group(['as' => 'products.', 'prefix' => 'product'], function () {
            Route::get('barcodes', [ProductController::class, 'barcode'])->name('barcode');
            Route::resource('variants', ProductVariantController::class);
            Route::resource('ingredients', ProductIngredientController::class);
            Route::resource('categories', ProductCategoryController::class);
            Route::resource('units', ProductUnitController::class);

            Route::resource('variant-stocks', ProductVariantStockController::class);
            Route::resource('ingredient-stocks', ProductIngredientStockController::class);
        });

        Route::resource('ingredients', IngredientController::class);
        Route::resource('ingredients-stocks', IngredientStockController::class);

        Route::resource('expenses', ExpenseController::class);
        Route::resource('purchases', PurchaseController::class);

        Route::resource('purchase-payments', PurchasePaymentController::class)->except(['create', 'store']);
        Route::get('purchase-payments/create/{purchase}', [PurchasePaymentController::class, 'create'])->name('purchase-payments.create');
        Route::post('purchase-payments/store/{purchase}', [PurchasePaymentController::class, 'store'])->name('purchase-payments.store');

        Route::resource('products', ProductController::class);
        Route::resource('users', UserController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('payment-methods', PaymentMethodController::class);
        Route::resource('daily-stocks', DailyStockController::class);
    });
    Route::get('transaction/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('transation/delete/multiple', [TransactionController::class, 'deleteMultiple'])->name('transactions.delete.multiple');
});

// Web Admin
Route::group(['as' => 'web.admin.', 'prefix' => 'web/admin'], function () {
    Route::group(['middleware' => ['auth']], function () {
        Route::get('dashboard', [WebminDashboardController::class, 'index'])->name('dashboard');
        Route::get('register-user', [RegisterUserController::class, 'index'])->name('register-user');
        Route::resource('news', NewsController::class);
    });
});

// Point of Sales
Route::group(['middleware' => ['auth'], 'as' => 'app.', 'prefix' => 'app'], function () {
    Route::get('point_of_sales', [POSController::class, 'index'])->name('point_of_sale');
});

// View and Download File route
Route::get('view/{filename}', [Controller::class, 'viewFile'])->name('web.view.file');
Route::get('download/{filename}', [Controller::class, 'downloadFile'])->name('web.download.file');
