<?php

namespace App\Providers;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceOrderController;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\OwnerDashboardRepository;
use App\Repositories\Eloquent\SaleRepository;
use App\Repositories\Eloquent\ServiceOrderRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Repositories\Interfaces\OwnerDashboardRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(CustomerRepository::class, CustomerRepository::class); // auto-resolved
        $this->app->bind(SaleRepository::class, SaleRepository::class);         // auto-resolved
        $this->app->bind(ServiceOrderRepository::class, ServiceOrderRepository::class); // auto-resolved

        $this->app->bind(
            OwnerDashboardRepositoryInterface::class,
            OwnerDashboardRepository::class
        );
        // Add more bindings here as features are built:
        // $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        // $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        // $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        // ── Authorization Gates ────────────────────────────────────────────────
        Gate::define('admin', fn ($user) => $user->isAdmin());
        Gate::define('owner', fn ($user) => $user->isOwner());
        Gate::define('admin_or_owner', fn ($user) => $user->isAdminOrOwner());
 
        // ── Share ke semua view ────────────────────────────────────────────────
        View::composer('*', function ($view) {
            $view->with('lowStockCount',   Product::lowStock()->count());
 
            // Badge pending WO di sidebar (hanya hitung kalau user login)
            $view->with('pendingWoCount',
                Auth::check()
                    ? ServiceOrder::whereIn('status', ['pending', 'in_progress'])->count()
                    : 0
            );
        });
    }
}
