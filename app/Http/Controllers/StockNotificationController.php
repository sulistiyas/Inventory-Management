<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAlertDismissal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockNotificationController extends Controller
{
    /**
     * Produk low stock yang belum di-dismiss user ini hari ini.
     * Dipanggil dari navbar bell icon via fetch setiap 5 menit.
     */
    public function getLowStock()
    {
        $dismissedProductIds = DB::table('stock_alert_dismissals')
            ->where('user_id', Auth::id())
            ->whereDate('dismissed_at', today())
            ->pluck('product_id')
            ->toArray();

        $products = Product::lowStock()
            ->whereNotIn('id', $dismissedProductIds)
            ->with('category:id,name')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'stock', 'min_stock', 'category_id']);

        return response()->json([
            'success' => true,
            'count'   => $products->count(),
            'data'    => $products->map(fn ($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'sku'          => $p->sku,
                'stock'        => $p->stock,
                'min_stock'    => $p->min_stock,
                'category'     => $p->category?->name,
            ]),
        ]);
    }

    /**
     * Dismiss notifikasi produk tertentu untuk hari ini.
     */
    public function dismiss(Product $product)
    {
        DB::table('stock_alert_dismissals')->updateOrInsert(
            [
                'user_id'    => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'dismissed_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Notifikasi \"{$product->name}\" disembunyikan untuk hari ini.",
        ]);
    }
}