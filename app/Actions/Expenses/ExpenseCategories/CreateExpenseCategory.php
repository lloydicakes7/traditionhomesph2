<?php

namespace App\Actions\Expenses\ExpenseCategories;

use App\Models\Admin;
use App\Models\Expense\ExpenseCategory;
use App\Services\ActivityLogService;
use App\Actions\Expenses\ExpenseCategories\Concerns\SyncsCategoryAccount;
use Illuminate\Support\Facades\DB;

class CreateExpenseCategory
{
    use SyncsCategoryAccount;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): ExpenseCategory
    {
        $category = DB::transaction(function () use ($data, $by) {
            $category = ExpenseCategory::create([
                'name'        => $data['name'],
                'description' => ($data['description'] ?? null) ?: null,
            ]);

            $this->syncAccount($category, $this->activity, $by->id);

            return $category;
        });

        $this->activity->log(
            $by,
            'created',
            'expense_category',
            "Added expense category {$category->name}",
            ['expense_category_id' => $category->id]
        );

        return $category;
    }
}
