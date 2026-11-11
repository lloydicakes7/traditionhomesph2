<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Queries\Reports\FinancialStatementQuery;
use App\Utility\PrintDocumentsHelper as Helper;

/**
 * The Financial Report — what the association earned and spent over a span of
 * its books, and the cash it held at the end of that span.
 *
 * The report is one sheet with two readings on it, and both are read from the
 * accounting module rather than computed here:
 *
 * 1. **Income and expenses.** The association's revenue accounts with what they
 *    earned, its expense accounts with what they spent, the two totals and the
 *    difference between them (see
 *    \App\Queries\Reports\FinancialStatementQuery::profitAndLoss()).
 *
 * 2. **The cash position.** Every account money sits in, with the balance its
 *    postings give it at the end of the span (see
 *    \App\Queries\Reports\FinancialStatementQuery::cashFlow()), split
 *    into the money in the bank and the money in the cash box. The cash box is
 *    the association's own cash-on-hand account (see
 *    \App\Models\Account\Account::isCashBox()); every other cash account is
 *    money deposited, which is what the report subtracts from the total cash
 *    to arrive at the cash on hand.
 *
 * The span is the form's own parameter — a day, that day's month, or its whole
 * year — and is resolved in one place (:period()) so the heading the paper
 * prints and the dates the books are read over can never disagree.
 */
final class FinancialReportBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Reports\FinancialStatementQuery */
    private $statements;

    public function __construct(PrintDocument $document, FinancialStatementQuery $statements)
    {
        parent::__construct($document);

        $this->statements = $statements;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $period = $this->period($params);

        $result = $this->statements->profitAndLoss($period['from'], $period['to']);

        return [
            'period'   => $period,
            'income'   => $this->section($result['revenue'], $result['total_revenue']),
            'expenses' => $this->section($result['expenses'], $result['total_expenses']),
            'net'      => [
                'amount' => Helper::money($result['net_income']),
            ],
            'cash'     => $this->cash($period['from'], $period['to']),
        ];
    }

    // =====================================================================
    // The span the report is drawn over
    // =====================================================================

    /**
     * The dates the books are read over, from the span and the date the form
     * collected: the day itself, the whole month it falls in, or its whole
     * year.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, string>  from, to, label and heading
     */
    private function period(array $params): array
    {
        $date = $this->dateOf($params, 'periodDate', now());

        if ($this->stringOf($params, 'periodType') === 'day') {
            return [
                'from'    => $date->toDateString(),
                'to'      => $date->toDateString(),
                'label'   => $date->format('F j, Y'),
                'heading' => 'For ' . $date->format('F j, Y'),
            ];
        }

        if ($this->stringOf($params, 'periodType') === 'year') {
            return [
                'from'    => $date->copy()->startOfYear()->toDateString(),
                'to'      => $date->copy()->endOfYear()->toDateString(),
                'label'   => $date->format('Y'),
                'heading' => 'For the year ' . $date->format('Y'),
            ];
        }

        return [
            'from'    => $date->copy()->startOfMonth()->toDateString(),
            'to'      => $date->copy()->endOfMonth()->toDateString(),
            'label'   => $date->format('F Y'),
            'heading' => 'For the month of ' . $date->format('F Y'),
        ];
    }

    // =====================================================================
    // Income and expenses
    // =====================================================================

    /**
     * One side of the report: every account of the type with what it came to,
     * and the section's own total.
     *
     * An account with nothing posted over the span is still a line of the
     * chart the office reads, so it prints with a zero rather than being
     * dropped — the section's total is what the section is read for.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function section(array $rows, string $total): array
    {
        $printed = [];

        foreach ($rows as $row) {
            $printed[] = [
                'code'    => (string) $row['account']->code,
                'account' => (string) $row['account']->name,
                'amount'  => Helper::money($row['signed']),
            ];
        }

        return [
            'rows'  => $printed,
            'total' => Helper::money($total),
        ];
    }

    // =====================================================================
    // The cash position
    // =====================================================================

    /**
     * The cash accounts with the balance each holds at the end of the span, and
     * the three lines the report comes to: the money in total, the money in the
     * bank, and the cash on hand.
     *
     * @return array<string, mixed>
     */
    private function cash(string $from, string $to): array
    {
        $flow = $this->statements->cashFlow($from, $to);

        $rows = [];
        $inBank = '0.00';

        foreach ($flow['rows'] as $row) {
            $onHand = $row['account']->isCashBox();

            if (! $onHand) {
                $inBank = bcadd($inBank, $row['closing'], 2);
            }

            $rows[] = [
                'code'    => (string) $row['account']->code,
                'account' => (string) $row['account']->name,
                'where'   => $onHand ? 'On hand' : 'In bank',
                'amount'  => Helper::money($row['closing']),
            ];
        }

        return [
            'rows'    => $rows,
            'total'   => Helper::money($flow['closing']),
            'in_bank' => Helper::money($inBank),
            'on_hand' => Helper::money(bcsub($flow['closing'], $inBank, 2)),
        ];
    }
}
