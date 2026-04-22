<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'is_active',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * A supplier can supply many products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Full contact string for display.
     */
    public function getContactSummaryAttribute(): string
    {
        $parts = array_filter([$this->phone, $this->email]);
        return implode(' · ', $parts) ?: '—';
    }

    /**
     * Location summary (city or address fallback).
     */
    public function getLocationAttribute(): string
    {
        return $this->city ?? ($this->address ? Str::limit($this->address, 40) : '—');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}