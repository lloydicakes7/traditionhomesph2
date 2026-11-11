<?php

namespace App\Queries\Reports;

use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Account\Account;
use App\Models\JournalEntry\JournalLine;

/**
 * The month-by-account roll-ups the income and expense reports read.
 *
 * Both answer the same underlying question — what was posted, where, and when
 * — and read it from `journal_lines`, the one place a posting lives (see
 * \App\Models\JournalEntry\JournalEntry::post()). Nothing here stores or
 * recomputes a figure of its own beyond adding the postings up, which is what
 * makes the reports and the chart of accounts agree down to the centavo.
 *
 * The roll-ups sit together because they are the same read of the same lines,
 * grouped differently; keeping them beside each other keeps the income and
 * expense reports on the same basis.
 */
class LedgerReportQuery
{
    // =====================================================================
    // The roll-ups the income and expense reports read
    // =====================================================================

    /**
     * What the accounts of the given types were debited and credited in a year,
     * by account and by month — the one read behind the monthly income and
     * monthly expense reports, so the two sit side by side on the same basis.
     *
     * @param  array<int, \App\Enums\AccountType>  $types
     * @return array<string, mixed>  totals, months and the rows per account
     */
    public function movementByMonth(array $types, int $year): array
    {
        $values = array_map(function (AccountType $type) {
            return $type->value;
        }, $types);

        $accounts = Account::query()->whereIn('type', $values)->ordered()->get()->keyBy('id');

        // The lines themselves are read and added up in PHP rather than by a
        // database date function, so the report reads the same on MySQL and on
        // the SQLite the test suite runs on.
        $lines = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::POSTED)
            ->whereIn('journal_lines.account_id', $accounts->keys()->all())
            ->whereYear('journal_entries.entry_date', $year)
            ->select('journal_lines.account_id', 'journal_lines.side', 'journal_lines.amount', 'journal_entries.entry_date')
            ->get();

        // Two arrays, on purpose: `$months` is the year's twelve months at zero
        // and is never written to, so every account's own column starts from
        // nothing, and `$totals` is what the months come to once each account
        // has been added in.
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[str_pad((string) $month, 2, '0', STR_PAD_LEFT)] = '0.00';
        }

        $totals = $months;

        $rows = [];
        $yearTotal = '0.00';

        foreach ($accounts as $account) {
            $amounts = $months;

            foreach ($lines->where('account_id', $account->id) as $line) {
                $month = substr((string) $line->entry_date, 5, 2);

                if (! array_key_exists($month, $amounts)) {
                    continue;
                }

                $lineAmount = bcadd((string) $line->amount, '0', 2);
                $amount = $account->isDebitNormal()
                    ? ($line->side === JournalLine::SIDE_DEBIT ? $lineAmount : bcsub('0.00', $lineAmount, 2))
                    : ($line->side === JournalLine::SIDE_CREDIT ? $lineAmount : bcsub('0.00', $lineAmount, 2));

                $amounts[$month] = bcadd($amounts[$month], $amount, 2);
            }

            $total = '0.00';

            foreach ($amounts as $amount) {
                $total = bcadd($total, $amount, 2);
            }

            $rows[] = [
                'account' => $account,
                'months'  => $amounts,
                'total'   => $total,
            ];

            $yearTotal = bcadd($yearTotal, $total, 2);

            foreach (array_keys($months) as $month) {
                $totals[$month] = bcadd($totals[$month], $amounts[$month], 2);
            }
        }

        return [
            'rows'       => $rows,
            'months'     => $totals,
            'year_total' => $yearTotal,

            // What the year averaged a month, and how many of its months moved
            // at all — the income and expense reports headline both, and
            // reading them here keeps the two on the same basis as the totals.
            'monthly_average'      => bcdiv($yearTotal, '12', 2),
            'months_with_movement' => count(array_filter($totals, function ($amount) {
                return bccomp($amount, '0.00', 2) !== 0;
            })),
        ];
    }

    /**
     * One account's posted movement on a single day, grouped by account — what
     * the daily income and daily expense reports read.
     *
     * @param  array<int, \App\Enums\AccountType>  $types
     * @param  string|null                         $sourceType  Only one source module, when given
     * @return array<string, mixed>
     */
    public function movementByDay(array $types, string $date, ?string $sourceType = null): array
    {
        $values = array_map(function (AccountType $type) {
            return $type->value;
        }, $types);

        $accounts = Account::query()->whereIn('type', $values)->ordered()->get()->keyBy('id');

        $lines = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::POSTED)
            ->whereIn('journal_lines.account_id', $accounts->keys()->all())
            ->whereDate('journal_entries.entry_date', $date)
            ->when($sourceType, function ($query) use ($sourceType) {
                $query->where('journal_entries.source_type', $sourceType);
            })
            ->selectRaw('journal_lines.account_id as account_id')
            ->selectRaw('journal_lines.side as side')
            ->selectRaw('SUM(journal_lines.amount) as total')
            ->groupBy('journal_lines.account_id', 'journal_lines.side')
            ->get();

        $rows = [];
        $total = '0.00';

        foreach ($accounts as $account) {
            $debits = '0.00';
            $credits = '0.00';

            foreach ($lines->where('account_id', $account->id) as $line) {
                if ($line->side === JournalLine::SIDE_DEBIT) {
                    $debits = bcadd((string) $line->total, '0', 2);
                } else {
                    $credits = bcadd((string) $line->total, '0', 2);
                }
            }

            $amount = $account->isDebitNormal()
                ? bcsub($debits, $credits, 2)
                : bcsub($credits, $debits, 2);

            if (bccomp($amount, '0.00', 2) === 0) {
                continue;
            }

            $rows[] = [
                'account' => $account,
                'amount'  => $amount,
            ];

            $total = bcadd($total, $amount, 2);
        }

        return ['rows' => $rows, 'total' => $total];
    }

    // =====================================================================
    // Helpers
    // =====================================================================

}
