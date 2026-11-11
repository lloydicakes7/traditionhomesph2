<?php

namespace App\Queries\Accounting;

use App\Enums\AccountKind;
use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Account\Account;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use Illuminate\Support\Collection;

/**
 * Reads the chart the way the office thinks about it: income, expenses and
 * cash. Money from outside the association is carried as an external
 * counterparty in the statements, not as an account on the chart.
 *
 * Every figure here is movement or a balance computed from posted journal
 * lines, never a stored column, so the chart can never disagree with the books.
 */
class ChartOfAccountsQuery
{
    // ===== Chart =====

    /**
     * The chart, grouped for the page.
     *
     * Income and expenses carry this year's total; cash accounts carry the
     * balance they hold today. The three groups always render, even when one
     * has no accounts yet.
     *
     * @return array<int, array<string, mixed>>
     */
    public function groups(string $search = ''): array
    {
        $accounts = Account::query()
            ->search($search)
            ->ordered()
            ->get();

        $allTime = $this->balances();
        $thisYear = $this->balances(
            now()->startOfYear()->toDateString(),
            now()->endOfYear()->toDateString()
        );

        $grouped = [];

        foreach ([AccountKind::INCOME(), AccountKind::EXPENSE(), AccountKind::MONEY()] as $kind) {
            $grouped[$kind->value] = [
                'key'   => $kind->value,
                'label' => $kind->heading(),
                'kind'  => $kind,
                'rows'  => [],
                'total' => '0.00',
            ];
        }

        foreach ($accounts as $account) {
            $kind = $account->kind();

            if (! $kind) {
                continue;
            }

            if ($kind->is(AccountKind::MONEY())) {
                $amount = $this->signedFrom($account, $allTime);
                $label = 'Balance';
            } elseif ($kind->is(AccountKind::INCOME())) {
                $totals = $thisYear[$account->id] ?? ['debits' => '0.00', 'credits' => '0.00'];
                $amount = bcsub($totals['credits'], $totals['debits'], 2);
                $label = 'Total This Year';
            } else {
                $totals = $thisYear[$account->id] ?? ['debits' => '0.00', 'credits' => '0.00'];
                $amount = bcsub($totals['debits'], $totals['credits'], 2);
                $label = 'Total This Year';
            }

            $row = $this->row($account, $amount, $label);
            $grouped[$kind->value]['rows'][] = $row;
            $grouped[$kind->value]['total'] = bcadd($grouped[$kind->value]['total'], $amount, 2);
        }

        $groups = [];

        foreach ($grouped as $group) {
            if ($search === '' || ! empty($group['rows'])) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    public function rowsOfTypes(array $types, ?string $from = null, ?string $to = null): array
    {
        $values = array_map(function (AccountType $type) {
            return $type->value;
        }, $types);

        $accounts = Account::query()
            ->whereIn('type', $values)
            ->ordered()
            ->get();

        $balances = $this->balances($from, $to);

        $rows = [];

        foreach ($accounts as $account) {
            $rows[] = $this->row($account, $this->signedFrom($account, $balances), 'Balance');
        }

        if (in_array(AccountType::EQUITY(), $types, true)) {
            $external = $this->externalBalance($from, $to);

            if (bccomp($external, '0.00', 2) !== 0) {
                $rows[] = [
                    'account' => new Account([
                        'code'    => '—',
                        'name'    => JournalEntry::EXTERNAL_ACCOUNT_NAME,
                        'type'    => AccountType::EQUITY(),
                        'is_cash' => false,
                    ]),
                    'amount'  => $external,
                    'signed'  => $external,
                    'label'   => 'Balance',
                ];
            }
        }

        return $rows;
    }

    // ===== Balances =====

    public function balances(?string $from = null, ?string $to = null): array
    {
        $rows = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::POSTED)
            ->whereNotNull('journal_lines.account_id')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('journal_entries.entry_date', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('journal_entries.entry_date', '<=', $to);
            })
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id as account_id')
            ->selectRaw("SUM(CASE WHEN journal_lines.side = ? THEN journal_lines.amount ELSE 0 END) as debits", [JournalLine::SIDE_DEBIT])
            ->selectRaw("SUM(CASE WHEN journal_lines.side = ? THEN journal_lines.amount ELSE 0 END) as credits", [JournalLine::SIDE_CREDIT])
            ->get();

        $balances = [];

        foreach ($rows as $row) {
            $balances[(int) $row->account_id] = [
                'debits'  => bcadd((string) ($row->debits ?: '0'), '0', 2),
                'credits' => bcadd((string) ($row->credits ?: '0'), '0', 2),
            ];
        }

        return $balances;
    }

    public function totalsFor(int $accountId, ?string $from = null, ?string $to = null): array
    {
        $balances = $this->balances($from, $to);

        return $balances[$accountId] ?? ['debits' => '0.00', 'credits' => '0.00'];
    }

    public function externalBalance(?string $from = null, ?string $to = null): string
    {
        $row = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::POSTED)
            ->whereNull('journal_lines.account_id')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('journal_entries.entry_date', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('journal_entries.entry_date', '<=', $to);
            })
            ->selectRaw("SUM(CASE WHEN journal_lines.side = ? THEN journal_lines.amount ELSE 0 END) as debits", [JournalLine::SIDE_DEBIT])
            ->selectRaw("SUM(CASE WHEN journal_lines.side = ? THEN journal_lines.amount ELSE 0 END) as credits", [JournalLine::SIDE_CREDIT])
            ->first();

        $debits = bcadd((string) ($row->debits ?: '0'), '0', 2);
        $credits = bcadd((string) ($row->credits ?: '0'), '0', 2);

        return bcsub($credits, $debits, 2);
    }

    public function signedBalance(Account $account, ?string $from = null, ?string $to = null): string
    {
        return $this->signedFrom($account, $this->balances($from, $to));
    }

    // ===== Options =====

    public function options(?AccountType $type = null): array
    {
        $query = Account::query()->notSystem();

        if ($type) {
            $query->ofType($type);
        }

        $options = [];

        foreach ($query->ordered()->get() as $account) {
            $options[$account->id] = $account->label;
        }

        return $options;
    }

    public function cashOptions(): array
    {
        $options = [];

        foreach (Account::query()->cash()->ordered()->get() as $account) {
            $options[$account->id] = $account->label;
        }

        return $options;
    }

    public function cashAccounts(): Collection
    {
        return Account::query()->cash()->ordered()->get();
    }

    // ===== Removal =====

    public function deletionSummary(Account $account): array
    {
        $lines = $account->journalLines()->count();
        $entries = $lines ? $account->journalLines()->distinct()->count('journal_entry_id') : 0;

        return array_values(array_filter([
            [
                'label' => 'Journal entries',
                'count' => $entries,
                'note'  => 'keep their lines and the account name they were posted under',
            ],
            [
                'label' => 'Journal lines',
                'count' => $lines,
                'note'  => 'lose their link to this account',
            ],
            [
                'label' => 'Payment methods',
                'count' => $account->paymentMethods()->count(),
                'note'  => 'are unlinked and fall back to cash on hand',
            ],
            [
                'label' => 'Expense labels',
                'count' => $account->expenseCategories()->count(),
                'note'  => 'are unlinked and fall back to unclassified expense',
            ],
            [
                'label' => 'Product labels',
                'count' => $account->productCategories()->count(),
                'note'  => 'are unlinked and fall back to fees income',
            ],
        ], function ($row) {
            return $row['count'] > 0;
        }));
    }

    // ===== Helpers =====

    private function row(Account $account, string $amount, string $label): array
    {
        return [
            'account' => $account,
            'amount'  => $amount,
            'signed'  => $amount,
            'label'   => $label,
        ];
    }

    private function signedFrom(Account $account, array $balances): string
    {
        $totals = $balances[$account->id] ?? ['debits' => '0.00', 'credits' => '0.00'];
        $net = bcsub($totals['debits'], $totals['credits'], 2);

        return $account->isDebitNormal() ? $net : bcsub('0.00', $net, 2);
    }
}
