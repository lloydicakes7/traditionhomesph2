<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Enums\ExpenseStatus;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Services\Accounting\ExpensePostingService;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class PostExpenseJournal
{
    private ActivityLogService $activity;

    private ExpensePostingService $posting;

    public function __construct(ActivityLogService $activity, ExpensePostingService $posting)
    {
        $this->activity = $activity;
        $this->posting = $posting;
    }

    // ===== Handle =====

    public function handle(Expense $expense, array $data, Admin $by): Expense
    {
        if (! $expense->status_enum || ! $expense->status_enum->is(ExpenseStatus::APPROVED())) {
            throw ValidationException::withMessages([
                'expense' => 'Approve the expense before posting it to the journal.',
            ]);
        }

        if ($expense->journal_entry_id) {
            return $expense;
        }

        $account = Account::query()->cash()->find((int) ($data['account_id'] ?? 0));

        if (! $account) {
            throw ValidationException::withMessages([
                'account_id' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }

        $entry = $this->posting->post($expense, $account, $by->id);

        $this->activity->log(
            $by,
            'posted',
            'expense',
            "Posted expense {$expense->reference} to journal entry {$entry->entry_no}",
            ['expense_id' => $expense->id, 'journal_entry_id' => $entry->id]
        );

        return $expense->fresh('journalEntry');
    }
}
