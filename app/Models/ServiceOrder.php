<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_id',
        'vehicle_plate',
        'vehicle_type',
        'complaint',
        'diagnosis',
        'notes',
        'service_fee',
        'discount',
        'status',
        'handled_by',
        'finished_at',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'discount'    => 'decimal:2',
        'finished_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /** Total spare part yang dipakai */
    public function partsCost(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    /** Total tagihan: jasa + parts - diskon */
    public function grandTotal(): float
    {
        return (float) ($this->service_fee + $this->partsCost() - $this->discount);
    }

    /** Generate nomor work order: SRV-YYYYMMDD-XXXX */
    public static function generateOrderNo(): string
    {
        $prefix = 'SRV-' . now()->format('Ymd') . '-';
        $last = static::where('order_no', 'like', $prefix . '%')
                      ->orderByDesc('order_no')
                      ->value('order_no');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
