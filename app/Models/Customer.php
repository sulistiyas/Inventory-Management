<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'vehicle_plate',
        'vehicle_type',
        'address',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    /** Cari berdasarkan nama, nomor HP, atau plat kendaraan */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'ilike', "%{$keyword}%")
              ->orWhere('phone', 'ilike', "%{$keyword}%")
              ->orWhere('vehicle_plate', 'ilike', "%{$keyword}%");
        });
    }
}
