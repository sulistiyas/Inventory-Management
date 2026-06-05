<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'user_id',
        'customer_id',
        'service_order_id',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'grand_total' => 'decimal:2',
        'sold_at'     => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sold_at', today());
    }

    /** Omzet harian: sum grand_total transaksi paid hari ini */
    public function scopeDailyRevenue($query, ?string $date = null)
    {
        $date ??= today()->toDateString();
        return $query->paid()->whereDate('sold_at', $date);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /** Total yang sudah dibayar */
    public function totalPaid(): float
    {
        return (float) $this->payments->sum('amount');
    }

    /** Sisa yang belum dibayar */
    public function remainingBalance(): float
    {
        return max(0, (float) $this->grand_total - $this->totalPaid());
    }

    /** Generate nomor invoice: INV-YYYYMMDD-XXXX */
    public static function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $last = static::whereRaw("invoice_no::text LIKE ?", [$prefix . '%'])
                    ->orderByDesc('invoice_no')
                    ->value('invoice_no');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
