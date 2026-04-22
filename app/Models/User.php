<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ── Constants ──────────────────────────────────────────────────────────────
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';

    const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_STAFF => 'Staff',
    ];

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    // ── Hidden from serialization ──────────────────────────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * A user can perform many stock movements.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ── Role helpers ───────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeStaff($query)
    {
        return $query->where('role', self::ROLE_STAFF);
    }
}