<?php

namespace App\Queries\Expenses;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierListQuery
{
    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return Supplier::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withCount('expenses')
            ->ordered()
            ->paginate((int) ($criteria['perPage'] ?? 15));
    }

    public function ids(array $criteria = []): array
    {
        return Supplier::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->pluck('id')
            ->all();
    }

    public function find(int $id): Supplier
    {
        return Supplier::findOrFail($id);
    }
}
