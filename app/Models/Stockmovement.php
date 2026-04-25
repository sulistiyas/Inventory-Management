<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    // ── Constants ──────────────────────────────────────────────────────────────
    const TYPE_IN         = 'in';
    const TYPE_OUT        = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';

    const TYPES = [
        self::TYPE_IN         => 'Stock In',
        self::TYPE_OUT        => 'Stock Out',
        self::TYPE_ADJUSTMENT => 'Adjustment',
    ];

    // Tipe yang boleh dipakai staff
    const STAFF_ALLOWED_TYPES = [
        self::TYPE_IN,
        self::TYPE_OUT,
    ];

    // ── Immutable — append-only audit log ─────────────────────────────────────
    public $timestamps = false;
    const UPDATED_AT   = null;

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

    // ── Model events ──────────────────────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(fn (StockMovement $m) => $m->created_at = now());

        static::updating(function () {
            throw new \LogicException('StockMovement records are immutable.');
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'Deleted User']);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    // Badge class — pakai naming dari datatable.css / app.css kamu
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN         => 'badge-success',
            self::TYPE_OUT        => 'badge-danger',
            self::TYPE_ADJUSTMENT => 'badge-warning',
            default               => 'badge-secondary',
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────
    public function scopeStockIn($query)      { return $query->where('type', self::TYPE_IN); }
    public function scopeStockOut($query)     { return $query->where('type', self::TYPE_OUT); }
    public function scopeAdjustment($query)   { return $query->where('type', self::TYPE_ADJUSTMENT); }

    public function scopeWithRelations($query)
    {
        return $query->with(['product:id,name,sku', 'user:id,name']);
    }

    public function scopeDateBetween($query, ?string $from, ?string $to)
    {
        $from && $query->whereDate('created_at', '>=', $from);
        $to   && $query->whereDate('created_at', '<=', $to);
        return $query;
    }
}