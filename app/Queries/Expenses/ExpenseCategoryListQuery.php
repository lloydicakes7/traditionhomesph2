<?php

namespace App\Queries\Expenses;

use App\Models\Expense\ExpenseCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseCategoryListQuery
{
    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return ExpenseCategory::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withCount('items')
            ->ordered()
            ->paginate((int) ($criteria['perPage'] ?? 15));
    }

    public function ids(array $criteria = []): array
    {
        return ExpenseCategory::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->pluck('id')
            ->all();
    }

    public function find(int $id): ExpenseCategory
    {
        return ExpenseCategory::findOrFail($id);
    }
}
