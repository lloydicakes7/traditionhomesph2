<?php

namespace App\Actions\Expenses\ExpenseCategories;

use App\Models\Admin;
use App\Models\Expense\ExpenseCategory;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteExpenseCategories
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $categories = ExpenseCategory::query()->whereIn('id', $ids)->withCount('items')->get();

        $used = $categories->filter(function (ExpenseCategory $category) {
            return $category->items_count > 0;
        });

        if ($used->isNotEmpty()) {
            throw ValidationException::withMessages([
                'delete' => 'Cannot delete ' . $used->pluck('name')->implode(', ')
                    . ' — expenses are already filed under it.',
            ]);
        }

        $deleted = DB::transaction(function () use ($categories) {
            foreach ($categories as $category) {
                $category->delete();
            }

            return $categories->count();
        });

        foreach ($categories as $category) {
            $this->activity->log(
                $by,
                'deleted',
                'expense_category',
                "Deleted expense category {$category->name} — its lines keep their snapshot",
                ['expense_category_id' => $category->id]
            );
        }

        return $deleted;
    }
}
