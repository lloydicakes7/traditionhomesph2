<?php

namespace App\Actions\Expenses\Expenses;

use App\Enums\ExpenseStatus;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use App\Actions\Expenses\Expenses\Concerns\ExpenseLines;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateExpense
{
    use ExpenseLines;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Expense $expense, array $data, Admin $by): Expense
    {
        if (! $expense->status_enum || ! $expense->status_enum->is(ExpenseStatus::DRAFT())) {
            throw ValidationException::withMessages([
                'expense' => 'Only a draft expense can be edited. An approved expense is locked to its journal entry.',
            ]);
        }

        DB::transaction(function () use ($expense, $data) {
            $supplier = ! empty($data['supplier_id']) ? Supplier::find($data['supplier_id']) : null;

            $expense->update([
                'expense_date' => $data['expense_date'],
                'supplier_id'  => $supplier ? $supplier->id : null,
                'description'  => ($data['description'] ?? null) ?: null,
            ]);

            $expense->items()->delete();
            $expense->items()->createMany($this->resolveItems($data['items'] ?? []));

            $expense->recalculate();
        });

        $this->activity->log(
            $by,
            'updated',
            'expense',
            "Updated expense {$expense->reference} ({$expense->subtotal_display})",
            ['expense_id' => $expense->id]
        );

        return $expense->fresh(['items']);
    }
}
