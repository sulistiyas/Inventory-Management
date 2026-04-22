<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_id',
        'supplier_id',
        'price',
        'stock',
        'min_stock',
        'is_active',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'stock'      => 'integer',
            'min_stock'  => 'integer',
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * A product belongs to one category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product belongs to one supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * A product has many stock movements (full audit log).
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    /**
     * Only stock-in movements.
     */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockMovement::class)
                    ->where('type', StockMovement::TYPE_IN)
                    ->latest();
    }

    /**
     * Only stock-out movements.
     */
    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockMovement::class)
                    ->where('type', StockMovement::TYPE_OUT)
                    ->latest();
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * True when current stock is at or below the minimum threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Human-readable stock status label.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock === 0) {
            return 'Out of Stock';
        }

        if ($this->isLowStock) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    /**
     * CSS badge class for stock status — used in Blade views.
     */
    public function getStockStatusClassAttribute(): string
    {
        return match (true) {
            $this->stock === 0   => 'badge-danger',
            $this->isLowStock    => 'badge-warning',
            default              => 'badge-success',
        };
    }

    /**
     * Formatted price with currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }

    /**
     * Total inventory value: price × stock.
     */
    public function getInventoryValueAttribute(): float
    {
        return (float) $this->price * $this->stock;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Products where stock <= min_stock (low stock alert).
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')
                     ->where('is_active', true);
    }

    /**
     * Products with zero stock.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0)->where('is_active', true);
    }

    /**
     * Filter by category.
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Filter by supplier.
     */
    public function scopeFromSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Full-text search across name, sku, and description.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
              ->orWhere('sku', 'ilike', "%{$term}%")
              ->orWhere('description', 'ilike', "%{$term}%");
        });
    }

    /**
     * Eager load relations needed for product listings.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['category', 'supplier']);
    }
}