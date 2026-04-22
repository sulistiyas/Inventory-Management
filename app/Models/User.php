<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
 
    const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_STAFF => 'Staff',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
