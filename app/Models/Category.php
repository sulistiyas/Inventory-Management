<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // ── Casts ──────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    /**
     * A category has many products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Count of active products in this category.
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->products()->active()->count();
    }

    // ── Model events — auto-generate slug from name ────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = self::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}