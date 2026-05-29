<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'price',
        'cost_price',
        'subtotal',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Hooks ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->subtotal = $item->qty * $item->price;
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /** Laba kotor per item */
    public function grossProfit(): float
    {
        if ($this->cost_price === null) {
            return 0.0;
        }
        return (float) (($this->price - $this->cost_price) * $this->qty);
    }
}
