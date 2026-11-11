<?php

namespace App\Queries\Reports;

use App\Enums\AccountType;
use App\Enums\JournalSource;
use App\Utility\PeriodHelper;

/**
 * What the association earned — by month across a year, and by day.
 *
 * Both readings come from the books rather than from the bills: income is what
 * was credited to the association's revenue accounts, so a month's income is
 * what the association actually charged and earned in it, and a day's income is
 * what the payments recorded that day took in. That is deliberate — it is the
 * same basis the monthly expense report reads, which is what lets the two sit
 * side by side and be compared.
 */
class IncomeReportQuery
{
    /** @var LedgerReportQuery */
    private $ledger;

    public function __construct(LedgerReportQuery $ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * The year's income, month by month and account by account.
     *
     * @return array<string, mixed>
     */
    public function monthly(int $year): array
    {
        $data = $this->ledger->movementByMonth([AccountType::INCOME()], $year);

        return [
            'year'       => $year,
            'rows'       => $data['rows'],
            'months'     => $data['months'],
            'year_total' => $data['year_total'],
            'month_labels' => $this->monthLabels($year),
            'best_month' => $this->bestMonth($data['months'], $year),
            'monthly_average'      => $data['monthly_average'],
            'months_with_movement' => $data['months_with_movement'],
        ];
    }

    /**
     * What a single day brought in, by income account.
     *
     * @return array<string, mixed>
     */
    public function daily(string $date): array
    {
        $data = $this->ledger->movementByDay([AccountType::INCOME()], $date, JournalSource::PAYMENT);

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
     * The month the year came to most in, as a billable month the association
     * writes anywhere else — "2026-03" — so its label is read the same way.
     *
     * @param  array<string, string>  $months
     * @return array{period: string, amount: string}|null
     */
    private function bestMonth(array $months, int $year): ?array
    {
        if (empty($months) || bccomp(max($months), '0.00', 2) <= 0) {
            return null;
        }

        $month = (string) array_search(max($months), $months, true);

        return ['period' => sprintf('%04d-%s', $year, $month), 'amount' => $months[$month]];
    }
}
