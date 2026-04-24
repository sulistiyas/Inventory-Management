<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
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

    // Profile
    // Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    // Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Products
    // Route::resource('products', ProductController::class);
    // Route::get('/products/filter/{filter}', [ProductController::class, 'index'])
    //      ->name('products.filter');

    // Categories
    // Route::resource('categories', CategoryController::class);

    // Suppliers
    // Route::resource('suppliers', SupplierController::class);

    // Stock management
    // Route::get('/stock',     [StockController::class, 'index'])->name('stock.index');
    // Route::get('/stock/in',  [StockController::class, 'createIn'])->name('stock.in');
    // Route::post('/stock/in', [StockController::class, 'storeIn'])->name('stock.store.in');
    // Route::get('/stock/out', [StockController::class, 'createOut'])->name('stock.out');
    // Route::post('/stock/out',[StockController::class, 'storeOut'])->name('stock.store.out');

    // User management — admin only
    // Route::middleware(['can:admin'])->group(function () {
    //     Route::resource('users', UserController::class);
    // });

});
