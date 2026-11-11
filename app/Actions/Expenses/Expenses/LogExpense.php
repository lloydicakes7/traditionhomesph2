<?php

namespace App\Actions\Expenses\Expenses;

use App\Enums\ExpenseStatus;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use App\Actions\Expenses\Expenses\Concerns\ExpenseLines;
use Illuminate\Support\Facades\DB;

class LogExpense
{
    use ExpenseLines;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Expense
    {
        $expense = DB::transaction(function () use ($data, $by) {
            $supplier = ! empty($data['supplier_id']) ? Supplier::find($data['supplier_id']) : null;

            $expense = Expense::create([
                'reference'           => $this->nextReference(),
                'expense_date'        => $data['expense_date'],
                'supplier_id'         => $supplier ? $supplier->id : null,
                'description'         => ($data['description'] ?? null) ?: null,
                'status'              => ExpenseStatus::DRAFT,
                'created_by_admin_id' => $by->id,
            ]);

            $expense->items()->createMany($this->resolveItems($data['items'] ?? []));

            $expense->recalculate();

            return $expense;
        });

        $this->activity->log(
            $by,
            'created',
            'expense',
            "Logged expense {$expense->reference} for {$expense->supplier_label} ({$expense->subtotal_display})",
            ['expense_id' => $expense->id]
        );

        return $expense->fresh(['items']);
    }
}
