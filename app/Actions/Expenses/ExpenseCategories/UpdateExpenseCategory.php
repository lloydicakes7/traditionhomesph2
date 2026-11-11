<?php

namespace App\Actions\Expenses\ExpenseCategories;

use App\Models\Admin;
use App\Models\Expense\ExpenseCategory;
use App\Services\ActivityLogService;
use App\Actions\Expenses\ExpenseCategories\Concerns\SyncsCategoryAccount;
use Illuminate\Support\Facades\DB;

class UpdateExpenseCategory
{
    use SyncsCategoryAccount;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(ExpenseCategory $category, array $data, Admin $by): ExpenseCategory
    {
        DB::transaction(function () use ($category, $data, $by) {
            $category->update([
                'name'        => $data['name'],
                'description' => ($data['description'] ?? null) ?: null,
            ]);

            $this->syncAccount($category, $this->activity, $by->id);
        });

        $this->activity->log(
            $by,
            'updated',
            'expense_category',
            "Updated expense category {$category->name}",
            ['expense_category_id' => $category->id]
        );

        return $category;
    }
}
