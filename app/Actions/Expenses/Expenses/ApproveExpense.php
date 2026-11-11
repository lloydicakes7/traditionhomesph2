<?php

namespace App\Actions\Expenses\Expenses;

use App\Enums\ExpenseStatus;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Services\ActivityLogService;

class ApproveExpense
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Expense $expense, Admin $by): Expense
    {
        if (! $expense->status_enum || ! $expense->status_enum->is(ExpenseStatus::DRAFT())) {
            return $expense;
        }

        $expense->update(['status' => ExpenseStatus::APPROVED]);

        $this->activity->log(
            $by,
            'approved',
            'expense',
            "Approved expense {$expense->reference}",
            ['expense_id' => $expense->id]
        );

        return $expense;
    }
}
