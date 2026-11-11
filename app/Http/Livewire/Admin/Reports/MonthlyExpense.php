<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\ExpenseReportQuery;
use App\Utility\CsvHelper;
use App\Utility\PeriodHelper;

/**
 * The Monthly Expense Report — the year's spending, its monthly trend, and what
 * each expense account took, so it can be laid beside the income report
 * figure for figure.
 */
class MonthlyExpense extends AdminComponent
{
    use ExportsReportCsv;

    /** @var int|string */
    public $year = '';

    /** @var ExpenseReportQuery */
    protected $expenses;

    public function boot(ExpenseReportQuery $expenses)
    {
        $this->expenses = $expenses;
    }

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    protected function reportKey(): string
    {
        return 'monthly-expenses';
    }

    protected function exportCsv(): string
    {
        $report = $this->expenses->monthly((int) $this->year);

        $rows = [];

        foreach ($report['rows'] as $row) {
            $rows[] = array_merge(
                [$row['account']->code, $row['account']->name],
                array_map(function ($amount) {
                    return number_format($amount, 2, '.', '');
                }, array_values($row['months'])),
                [number_format($row['total'], 2, '.', '')]
            );
        }

        $rows[] = array_merge(
            ['', 'TOTAL'],
            array_map(function ($amount) {
                return number_format($amount, 2, '.', '');
            }, array_values($report['months'])),
            [number_format($report['year_total'], 2, '.', '')]
        );

        return CsvHelper::build(
            array_merge(['Code', 'Account'], array_values($report['month_labels']), ['Total']),
            $rows
        );
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.monthly-expense', [
            'report' => $this->expenses->monthly((int) $this->year),
            'years'  => PeriodHelper::years([]),
        ]);
    }
}
