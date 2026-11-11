<?php

namespace App\Models\Product;

use App\Models\Account\Account;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'account_id',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The products and services filed under this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * The revenue account an item in this category earns when it is billed.
     *
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the category's name and note.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Order categories by name — the ordering every list uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
