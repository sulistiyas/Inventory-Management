<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Warehouse Inventory System
|--------------------------------------------------------------------------
*/

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'))->name('home');
Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/api/data', [CategoryController::class, 'list'])->name('categories.list');
        Route::post('/store', [CategoryController::class, 'store']);
        Route::put('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/destroy/{id}', [CategoryController::class, 'destroy']);
    });

    // Suppliers
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/api/data', [SupplierController::class, 'list'])->name('suppliers.list');
        Route::post('/store', [SupplierController::class, 'store']);
        Route::get('/{id}', [SupplierController::class, 'show']);
        Route::put('/update/{id}', [SupplierController::class, 'update']);
        Route::delete('/destroy/{id}', [SupplierController::class, 'destroy']);
    });

    
    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('/api/data', [ProductController::class, 'list'])->name('products.list');
        Route::post('/store', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/update/{id}', [ProductController::class, 'update']);
        Route::delete('/destroy/{id}', [ProductController::class, 'destroy']);
    });

    
    Route::prefix('stock')->name('stock.')->group(function () {
 
        // ── Web pages ─────────────────────────────────────────────────────────
        Route::get('/',[StockController::class, 'index'])->name('index');
        Route::get('/in',[StockController::class, 'createIn'])->name('in');
        Route::get('/out',[StockController::class, 'createOut'])->name('out');
        Route::get('/{movement}',[StockController::class, 'show'])->name('show');
        Route::get('/product/{product}/history', [StockController::class, 'productHistory'])->name('product.history');
    });
    
    // ── routes/api.php additions ───────────────────────────────────────────────
    
    Route::prefix('api')->group(function () {
    
        // Stock movements list (datatable)
        Route::get('/stock',[StockController::class, 'apiList'])->name('api.stock.list');
        Route::get('/stock/products', [StockController::class, 'apiProducts'])->name('api.stock.products');
        Route::get('/stock/{movement}',[StockController::class, 'apiShow'])->name('api.stock.show');
    
        // Transactions
        Route::post('/stock/in',[StockController::class, 'storeIn'])->name('api.stock.in');
        Route::post('/stock/out',[StockController::class, 'storeOut'])->name('api.stock.out');
    });
    
    
    // ── Stock Movements (new controller) ──────────────────────────────────────────
    
    Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
        Route::get('/',[StockMovementController::class, 'index'])->name('index');
        Route::get('/list',[StockMovementController::class, 'list'])->name('list');
        Route::post('/store',[StockMovementController::class, 'store'])->name('store');
    });

    // Profile
    // Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    // Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    

    // User management — admin only
    // Route::middleware(['can:admin'])->group(function () {
    //     Route::resource('users', UserController::class);
    // });

});
