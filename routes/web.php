    <?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Services\DashboardService;
use Illuminate\Http\Request;
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
        Route::get('/dashboard/chart', function (Request $request, DashboardService $service) {
            $days = (int) $request->get('days', 7);
            $days = in_array($days, [7, 14, 30]) ? $days : 7;

            return response()->json(
                $service->getDashboardData($days)['chartData']
            );
        })->name('dashboard.chart');

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
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::get('/in', [StockController::class, 'createIn'])->name('in');
            Route::get('/out', [StockController::class, 'createOut'])->name('out');
            Route::get('/product/{product}/history', [StockController::class, 'productHistory'])
                ->name('product.history');
            Route::get('/{movement}', [StockController::class, 'show'])
                ->name('show');
        });
        
        // ── routes/api.php additions ───────────────────────────────────────────────
        
        Route::prefix('api')->group(function () {
        
            Route::get('/stock', [StockController::class, 'apiList'])->name('api.stock.list');
            Route::get('/stock/products', [StockController::class, 'apiProducts'])
                ->name('api.stock.products');
            Route::get('/stock/{movement}', [StockController::class, 'apiShow'])
                ->name('api.stock.show');
            Route::post('/stock/in', [StockController::class, 'storeIn'])
                ->name('api.stock.store.in');
            Route::post('/stock/out', [StockController::class, 'storeOut'])
                ->name('api.stock.store.out');
            Route::get('/stock/product/{product}/history', [StockController::class, 'productHistory'])
                ->name('api.stock.product.history');
        });
        
        
        // ── Stock Movements (new controller) ──────────────────────────────────────────
        
        Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
            Route::get('/',[StockMovementController::class, 'index'])->name('index');
            Route::post('/store',[StockMovementController::class, 'store'])->name('store');
        });

        // ── Users (admin only) ────────────────────────────────────────────────────
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',[UserController::class, 'index'])->name('index');
            Route::get('/api/data',[UserController::class, 'list'])->name('list');
            Route::post('/store',[UserController::class, 'store'])->name('store');
            Route::put('/update/{id}',[UserController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}',[UserController::class, 'destroy'])->name('destroy');
            Route::post('/toggle-active/{id}',[UserController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('stock-summary', [ReportController::class, 'stockSummary'])->name('stock-summary');
            Route::get('stock-movement', [ReportController::class, 'stockMovement'])->name('stock-movement');
        });

        // ── Customers ─────────────────────────────────────────────────────────────
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/api/data', [CustomerController::class, 'list'])->name('list');
            Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
            Route::post('/store', [CustomerController::class, 'store'])->name('store');
            Route::put('/update/{id}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        // ── Sales (POS kasir) ──────────────────────────────────────────────────────
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/api/data', [SaleController::class, 'list'])->name('list');
            Route::get('/{id}', [SaleController::class, 'show'])->name('show');
            Route::post('/store', [SaleController::class, 'store'])->name('store');
            Route::post('/cancel/{id}', [SaleController::class, 'cancel'])->name('cancel');
            Route::get('/summary/daily', [SaleController::class, 'dailySummary'])->name('daily-summary');
        });

        // ── API khusus sales (products dropdown kasir) ────────────────────────────
        Route::prefix('api')->group(function () {
            Route::get('/sales/products', [SaleController::class, 'apiProducts'])->name('api.sales.products');
        });

        // ── Service Orders (work order bengkel) ───────────────────────────────────
        Route::prefix('service-orders')->name('service-orders.')->group(function () {
            Route::get('/', [ServiceOrderController::class, 'index'])->name('index');
            Route::get('/api/data', [ServiceOrderController::class, 'list'])->name('list');
            Route::get('/{id}', [ServiceOrderController::class, 'show'])->name('show');
            Route::post('/store', [ServiceOrderController::class, 'store'])->name('store');
            Route::put('/update/{id}', [ServiceOrderController::class, 'update'])->name('update');
            Route::post('/update-status/{id}', [ServiceOrderController::class, 'updateStatus'])->name('update-status');
        });

    });
