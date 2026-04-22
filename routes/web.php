<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Warehouse Inventory System
|--------------------------------------------------------------------------
*/

// // ── Auth ──────────────────────────────────────────────────────────────────────
// Route::get('/',       fn () => redirect()->route('dashboard'));
// Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
// Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// // ── Authenticated routes ───────────────────────────────────────────────────────
// Route::middleware(['auth'])->group(function () {

//     // Dashboard
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//     // Profile
//     Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
//     Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

//     // Products
//     Route::resource('products', ProductController::class);
//     Route::get('/products/filter/{filter}', [ProductController::class, 'index'])
//          ->name('products.filter');

//     // Categories
//     Route::resource('categories', CategoryController::class);

//     // Suppliers
//     Route::resource('suppliers', SupplierController::class);

//     // Stock management
//     Route::get('/stock',     [StockController::class, 'index'])->name('stock.index');
//     Route::get('/stock/in',  [StockController::class, 'createIn'])->name('stock.in');
//     Route::post('/stock/in', [StockController::class, 'storeIn'])->name('stock.store.in');
//     Route::get('/stock/out', [StockController::class, 'createOut'])->name('stock.out');
//     Route::post('/stock/out',[StockController::class, 'storeOut'])->name('stock.store.out');

//     // User management — admin only
//     Route::middleware(['can:admin'])->group(function () {
//         Route::resource('users', UserController::class);
//     });

// });

// Dashboard
    Route::redirect('/', 'dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');