<?php

namespace App\Models\Product;

use App\Models\Bill\BillItem;
use App\Enums\ProductType;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_category_id',
        'name',
        'type',
        'price',
        'unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The category this item is filed under, when it has one.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * The bill lines billed from this item.
     */
    public function billItems(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the item's name and description.
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
     * Only the items still offered when billing.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order items by name — the ordering every list uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The ProductType enum instance for this item.
     */
    public function getTypeEnumAttribute(): ?ProductType
    {
        return $this->type ? ProductType::tryFrom($this->type) : null;
    }

    /**
     * The price as a list writes it: "₱150.00".
     */
    public function getPriceDisplayAttribute(): string
    {
        return MoneyHelper::format($this->price);
    }

    /**
     * What the item costs, with its unit when it has one — "₱150.00 / per
     * sticker".
     */
    public function getPriceWithUnitAttribute(): string
    {
        return $this->price_display . ($this->unit ? ' / ' . $this->unit : '');
    }
}
