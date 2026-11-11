<?php

namespace App\Actions\Expenses\Expenses;

use App\Enums\ExpenseStatus;
use App\Enums\JournalSource;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteExpenses
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $expenses = Expense::query()->whereIn('id', $ids)->with('journalEntry.lines')->get();

        $deleted = DB::transaction(function () use ($expenses, $by) {
            foreach ($expenses as $expense) {
                $this->reverseEntry($expense, $by);
            }

            return Expense::whereIn('id', $expenses->pluck('id')->all())->delete();
        });

        foreach ($expenses as $expense) {
            $this->activity->log(
                $by,
                'deleted',
                'expense',
                "Deleted expense {$expense->reference}",
                ['expense_id' => $expense->id]
            );
        }

        return $deleted;
    }

    // ===== Helpers =====

    private function reverseEntry(Expense $expense, Admin $by): void
    {
        $entry = $expense->journalEntry;

        if (! $entry || ! $expense->status_enum || ! $expense->status_enum->is(ExpenseStatus::APPROVED())) {
            return;
        }

        JournalEntry::post([
            'entry_date'     => $entry->entry_date,
            'classification' => $entry->classification,
            'source_type'    => JournalSource::EXPENSE,
            'source_id'      => $expense->id,
            'reference_no'   => $expense->reference,
            'memo'           => 'Reversal of expense ' . $expense->reference,
            'lines'          => $entry->lines->map(function (JournalLine $line) use ($expense) {
                return [
                    'account_id' => $line->account_id,
                    'side'       => $line->side === JournalLine::SIDE_DEBIT
                        ? JournalLine::SIDE_CREDIT
                        : JournalLine::SIDE_DEBIT,
                    'amount'     => (float) $line->amount,
                    'memo'       => 'Reversal of ' . $expense->reference,
                ];
            })->all(),
        ], $by->id);
    }
}
