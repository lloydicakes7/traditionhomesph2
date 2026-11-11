<?php

namespace App\Services\Accounting;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Expense\Expense;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpensePostingService
{
    // ===== Operations =====

    public function post(Expense $expense, ?Account $money, int $adminId): JournalEntry
    {
        if ($expense->journal_entry_id) {
            return $expense->journalEntry()->firstOrFail();
        }

        if (! $money || ! $money->is_cash) {
            throw ValidationException::withMessages([
                'account_id' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }

        $expense->loadMissing('items.category');

        return DB::transaction(function () use ($expense, $money, $adminId) {
            $entry = JournalEntry::post([
                'entry_date'     => $expense->expense_date ?: now(),
                'classification' => JournalClassification::STANDARD,
                'source_type'    => JournalSource::EXPENSE,
                'source_id'      => $expense->id,
                'reference_no'   => $expense->reference,
                'memo'           => 'Expense ' . $expense->reference
                    . ($expense->supplier ? ' — ' . $expense->supplier->name : ''),
                'lines'          => $this->lines($expense, $money),
            ], $adminId);

            $expense->update(['journal_entry_id' => $entry->id]);

            return $entry;
        });
    }

    // ===== Helpers =====

    private function lines(Expense $expense, Account $money): array
    {
        $lines = [];
        $total = '0.00';

        foreach ($expense->items as $item) {
            $account = Account::forExpenseCategory($item->category);
            $amount = bcadd((string) $item->amount, '0', 2);

            if (bccomp($amount, '0.00', 2) <= 0) {
                continue;
            }

            $total = bcadd($total, $amount, 2);

            if (isset($lines[$account->id])) {
                $lines[$account->id]['amount'] = bcadd($lines[$account->id]['amount'], $amount, 2);

                continue;
            }

            $lines[$account->id] = [
                'account_id' => $account->id,
                'side'       => JournalLine::SIDE_DEBIT,
                'amount'     => $amount,
                'memo'       => $item->description,
            ];
        }

        $lines[] = [
            'account_id' => $money->id,
            'side'       => JournalLine::SIDE_CREDIT,
            'amount'     => $total,
            'memo'       => 'Paid from ' . $money->name,
        ];

        return array_values($lines);
    }
}
