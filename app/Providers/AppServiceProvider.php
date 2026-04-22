<?php

namespace App\Providers;

use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Interfaces\DashboardRepositoryInterface;
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

        // Add more bindings here as features are built:
        // $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        // $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        // $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        // $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Share low stock count with ALL views (for sidebar badge + navbar bell) ──
        View::composer('*', function ($view) {
            // if (auth()->check()) {
                $lowStockCount = \App\Models\Product::lowStock()->count();
                $view->with('lowStockCount', $lowStockCount);
            // }
        });
    }
}
