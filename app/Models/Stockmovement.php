<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    // ── Constants ──────────────────────────────────────────────────────────────
    const TYPE_IN  = 'in';
    const TYPE_OUT = 'out';

    const TYPES = [
        self::TYPE_IN  => 'Stock In',
        self::TYPE_OUT => 'Stock Out',
    ];

    // ── Immutable — this is an append-only audit log ───────────────────────────
    public $timestamps = false;         // We manage created_at manually
    const UPDATED_AT   = null;          // No updated_at column on this table

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'created_at',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'stock_before' => 'integer',
            'stock_after'  => 'integer',
            'created_at'   => 'datetime',
        ];
    }

    // ── Model events — set created_at, block updates ───────────────────────────
    protected static function booted(): void
    {
        // Always stamp created_at on creation
        static::creating(function (StockMovement $movement) {
            $movement->created_at = now();
        });

        // Hard guard: prevent any update to a movement record
        static::updating(function () {
            throw new \LogicException(
                'StockMovement records are immutable. Create a corrective movement instead.'
            );
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * The product this movement belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The user who performed this movement (nullable — user may be deleted).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User',
        ]);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Human-readable movement type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * CSS class for the movement type badge.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN  => 'badge-success',
            self::TYPE_OUT => 'badge-danger',
            default        => 'badge-secondary',
        };
    }

    /**
     * Signed quantity — positive for 'in', negative for 'out'.
     */
    public function getSignedQuantityAttribute(): int
    {
        return $this->type === self::TYPE_IN
            ? $this->quantity
            : -$this->quantity;
    }

    /**
     * Was this a stock increase?
     */
    public function isStockIn(): bool
    {
        return $this->type === self::TYPE_IN;
    }

    /**
     * Was this a stock decrease?
     */
    public function isStockOut(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeStockIn($query)
    {
        return $query->where('type', self::TYPE_IN);
    }

    public function scopeStockOut($query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter by date range — used in reports.
     */
    public function scopeDateBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Recent movements — latest first.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest('created_at')->limit($limit);
    }

    /**
     * Eager load product + user for movement listings.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['product.category', 'user']);
    }
}