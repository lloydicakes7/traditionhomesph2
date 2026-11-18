<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Services\Accounting\ExpensePostingService;
use App\Services\ActivityLogService;

/**
 * Post an expense to the books.
 *
 * The entry is the one accounting confirmed in the posting dialog, starting
 * from the draft the system wrote for the expense. The expense is the source:
 * the service stamps it on the entry and links the two, and the money side must
 * still come out of a cash account.
 */
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

    public function handle(Expense $expense, array $entry, Admin $by): Expense
    {
        if ($expense->journal_entry_id) {
            return $expense;
        }

        $posted = $this->posting->post($expense, $entry, $by->id);

        $this->activity->log(
            $by,
            'posted',
            'expense',
            "Posted expense {$expense->reference} to journal entry {$posted->entry_no}",
            ['expense_id' => $expense->id, 'journal_entry_id' => $posted->id]
        );

        return $expense->fresh('journalEntry');
    }
}
