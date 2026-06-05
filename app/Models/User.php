<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Constants ──────────────────────────────────────────────────────────────
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_OWNER = 'owner';   // ← baru

    const ROLES = [
        self::ROLE_OWNER => 'Owner',           // ← baru
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_STAFF => 'Staff',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function serviceOrdersHandled(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'handled_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'user_id');
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

    public function isOwner(): bool        // ← baru
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Owner dan Admin sama-sama punya akses manajemen.
     */
    public function isAdminOrOwner(): bool  // ← baru
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OWNER]);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereRaw('"is_active" = TRUE');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeStaff($query)
    {
        return $query->where('role', self::ROLE_STAFF);
    }

    public function scopeOwners($query)    // ← baru
    {
        return $query->where('role', self::ROLE_OWNER);
    }
}