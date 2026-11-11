<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\AccountType;
use App\Enums\PrintDocument;
use App\Queries\Reports\ExpenseReportQuery;
use App\Queries\Reports\IncomeReportQuery;
use App\Utility\PrintDocumentsHelper as Helper;

/**
 * The Summary Report — one side of the year's books, account by account and
 * month by month, on a single sheet.
 *
 * The sheet is a matrix: a row per account, the twelve months as its columns,
 * a total column at the end of every row and a total row under every column,
 * so a year can be read in either direction — which account the year came from,
 * and which months it came in.
 *
 * Rows are read from the association's income accounts or from its expense
 * accounts, whichever the form asked for, and the twelve months come from the
 * month-by-account roll-up the Monthly Income and Monthly Expense reports read
 * (see \App\Queries\Reports\IncomeReportQuery::monthly() and
 * \App\Queries\Reports\ExpenseReportQuery::monthly()) — so the paper
 * and those two pages can never disagree about a month's income.
 *
 * An account with nothing posted all year is left off: it would print a line of
 * twelve dashes and add nothing to either total.
 */
final class SummaryReportBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Reports\IncomeReportQuery */
    private $income;

    /** @var \App\Queries\Reports\ExpenseReportQuery */
    private $expense;

    public function __construct(
        PrintDocument $document,
        IncomeReportQuery $income,
        ExpenseReportQuery $expense
    ) {
        parent::__construct($document);

        $this->income = $income;
        $this->expense = $expense;
    }

    /**
     * The report runs across the sheet — thirteen money columns and an account
     * are wider than the page is tall.
     */
    public function orientation(): string
    {
        return 'landscape';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $type = AccountType::from($this->stringOf($params, 'accountType'));
        $year = (int) $this->stringOf($params, 'year');

        $report = $type->is(AccountType::INCOME())
            ? $this->income->monthly($year)
            : $this->expense->monthly($year);

        return [
            'type'         => $type->label(),
            'heading'      => $type->heading() . ' BY MONTH',
            'year'         => (string) $year,
            'span'         => 'For the year ' . $year,
            'currency'     => Helper::MONEY_PREFIX,
            'month_labels' => $report['month_labels'],
            'rows'         => $this->rows($report['rows']),
            'months'       => $this->amounts($report['months']),
            'year_total'   => $this->amount($report['year_total']),
        ];
    }

    // =====================================================================
    // The matrix
    // =====================================================================

    /**
     * The year's accounts, one line each, with their months and their total.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function rows(array $rows): array
    {
        $printed = [];

        foreach ($rows as $row) {
            if (! $this->moved($row['months'])) {
                continue;
            }

            $printed[] = [
                'code'    => (string) $row['account']->code,
                'account' => (string) $row['account']->name,
                'months'  => $this->amounts($row['months']),
                'total'   => $this->amount($row['total']),
            ];
        }

        return $printed;
    }

    /**
     * Whether a row moved at all over the year.
     *
     * @param  array<string, float>  $months
     */
    private function moved(array $months): bool
    {
        foreach ($months as $amount) {
            if (abs((float) $amount) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * A line of monthly amounts, with the months nothing was posted in left as
     * a dash — thirteen money columns are read by the figures in them.
     *
     * @param  array<string, float>  $amounts
     * @return array<int, string>
     */
    private function amounts(array $amounts): array
    {
        $printed = [];

        foreach ($amounts as $amount) {
            $printed[] = $this->amount($amount);
        }

        return $printed;
    }

    /**
     * One amount, written without the currency: the sheet's own line names
     * `PHP` once rather than thirteen times.
     */
    private function amount($amount): string
    {
        $amount = (float) $amount;

        return abs($amount) < 0.005 ? '—' : number_format($amount, 2);
    }
}
