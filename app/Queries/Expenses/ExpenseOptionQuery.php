<?php

namespace App\Queries\Expenses;

use App\Models\Expense\ExpenseCategory;
use App\Models\Supplier;
use App\Utility\PeriodHelper;
use Illuminate\Database\Eloquent\Collection;

class ExpenseOptionQuery
{
    // ===== Options =====

    public function categories(): Collection
    {
        return ExpenseCategory::query()->ordered()->get();
    }

    public function suppliers(): Collection
    {
        return Supplier::query()->ordered()->get();
    }

    public function months(int $back = 12): array
    {
        return PeriodHelper::recentMonths($back);
    }
}
