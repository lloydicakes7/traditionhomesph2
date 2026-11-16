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

    public function post(Expense $expense, array $entry, int $adminId): JournalEntry
    {
        if ($expense->journal_entry_id) {
            return $expense->journalEntry()->firstOrFail();
        }

        $this->assertMoneyAccount($entry['lines']);

        $entry = array_merge($entry, [
            'source_type' => JournalSource::EXPENSE,
            'source_id'   => $expense->id,
        ]);

        return DB::transaction(function () use ($expense, $entry, $adminId) {
            $posted = JournalEntry::post($entry, $adminId);

            $expense->update(['journal_entry_id' => $posted->id]);

            return $posted;
        });
    }

    /**
     * The entry this expense writes, before it reaches the books.
     *
     * The posting modal opens on these figures, so the officer starts from the
     * entry the system would post and may change it before confirming.
     *
     * @return array<string, mixed>
     */
    public function draft(Expense $expense, ?Account $money): array
    {
        if (! $money || ! $money->is_cash) {
            throw ValidationException::withMessages([
                'account_id' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }

        $expense->loadMissing('items.category');

        return [
            'entry_date'     => $expense->expense_date ?: now(),
            'classification' => JournalClassification::STANDARD,
            'source_type'    => JournalSource::EXPENSE,
            'source_id'      => $expense->id,
            'reference_no'   => $expense->reference,
            'memo'           => 'Expense ' . $expense->reference
                . ($expense->supplier ? ' — ' . $expense->supplier->name : ''),
            'lines'          => $this->lines($expense, $money),
        ];
    }

    // ===== Helpers =====

    /**
     * Refuse an entry that never names the cash or bank the money came out of.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function assertMoneyAccount(array $lines): void
    {
        $ids = array_map(function (array $line) {
            return (int) ($line['account_id'] ?? 0);
        }, $lines);

        if (! Account::query()->cash()->whereIn('id', $ids)->exists()) {
            throw ValidationException::withMessages([
                'lines' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }
    }

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
