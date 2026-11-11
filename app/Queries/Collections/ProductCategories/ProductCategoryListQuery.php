<?php

namespace App\Queries\Collections\ProductCategories;

use App\Models\Product\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductCategoryListQuery
{
    // ===== Rows =====

    public function find(int $id): ProductCategory
    {
        return ProductCategory::findOrFail($id);
    }

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return ProductCategory::query()
            ->withCount('products')
            ->search((string) ($criteria['search'] ?? ''))
            ->ordered()
            ->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        return ProductCategory::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->pluck('id')
            ->all();
    }
}
