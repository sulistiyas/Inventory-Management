<?php

namespace App\Providers;

use App\Models\Product;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // ── Authorization Gates ────────────────────────────────────────────────────
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // ── Share low stock count with ALL views (for sidebar badge + navbar bell) ──
        View::composer('*', function ($view) {
            // if (auth()->check()) {
                $view->with('lowStockCount', \App\Models\Product::lowStock()->count());
            // }
        });
    }
}
