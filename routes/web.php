<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/categories', fn () => view('categories.index'))->name('categories.index');
    Route::get('/api/categories', [CategoryController::class, 'list'])->name('api.categories.list');
    Route::post('/api/categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::get('/api/categories/{id}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::put('/api/categories/{id}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/api/categories/{id}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

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
