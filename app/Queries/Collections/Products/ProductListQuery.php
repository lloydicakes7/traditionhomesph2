<?php

namespace App\Queries\Collections\Products;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductListQuery
{
    const SORTABLE = ['name', 'price', 'type'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with('category');

        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy(in_array($field, self::SORTABLE, true) ? $field : 'name', $direction);

        return $query->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function find(int $id): Product
    {
        return Product::with('category')->findOrFail($id);
    }

    public function billable(): Collection
    {
        return Product::query()->active()->with('category')->ordered()->get();
    }

    public function hasBillable(): bool
    {
        return Product::query()->active()->exists();
    }

    // ===== Options =====

    public function categoryOptions(): array
    {
        return ProductCategory::query()->ordered()->pluck('name', 'id')->all();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return Product::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($filters['category'] ?? null, function ($query, $category) {
                $query->where('product_category_id', $category);
            });
    }
}
