<?php

namespace App\Queries\Reports;

use App\Enums\AccountType;
use App\Enums\JournalSource;
use App\Utility\PeriodHelper;

/**
 * What the association spent — by month across a year, and by day.
 *
 * Spending is read the same way income is: what was debited to the
 * association's expense accounts in the books, so the two reports are two sides
 * of one ledger and can be laid side by side without either needing an
 * adjustment. A month's spending is what was charged to expense in it; a day's
 * spending is what the expenses approved that day paid out.
 */
class ExpenseReportQuery
{
    /** @var LedgerReportQuery */
    private $ledger;

    public function __construct(LedgerReportQuery $ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * The year's spending, month by month and account by account.
     *
     * @return array<string, mixed>
     */
    public function monthly(int $year): array
    {
        $data = $this->ledger->movementByMonth([AccountType::EXPENSE()], $year);

        return [
            'year'         => $year,
            'rows'         => $data['rows'],
            'months'       => $data['months'],
            'year_total'   => $data['year_total'],
            'month_labels' => $this->monthLabels($year),
            'highest_month' => $this->highestMonth($data['months'], $year),
            'monthly_average'      => $data['monthly_average'],
            'months_with_movement' => $data['months_with_movement'],
        ];
    }

    /**
     * What a single day spent, by expense account.
     *
     * @return array<string, mixed>
     */
    public function daily(string $date): array
    {
        $data = $this->ledger->movementByDay([AccountType::EXPENSE()], $date, JournalSource::EXPENSE);

        return [
            'date'  => $date,
            'rows'  => $data['rows'],
            'total' => $data['total'],
        ];
    }

    /**
     * The months of a year as headings: "01" => "Jan".
     *
     * @return array<string, string>
     */
    private function monthLabels(int $year): array
    {
        $labels = [];

        for ($month = 1; $month <= 12; $month++) {
            $key = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

            $labels[$key] = PeriodHelper::shortLabel($year . '-' . $key);
        }

        return $labels;
    }

    /**
     * The month the year spent most in, as a billable month the association
     * writes anywhere else — "2026-03" — so its label is read the same way.
     *
     * @param  array<string, string>  $months
     * @return array{period: string, amount: string}|null
     */
    private function highestMonth(array $months, int $year): ?array
    {
        if (empty($months) || bccomp(max($months), '0.00', 2) <= 0) {
            return null;
        }

        $month = (string) array_search(max($months), $months, true);

        return ['period' => sprintf('%04d-%s', $year, $month), 'amount' => $months[$month]];
    }
}
