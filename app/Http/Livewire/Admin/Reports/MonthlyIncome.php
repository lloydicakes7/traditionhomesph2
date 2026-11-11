<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\IncomeReportQuery;
use App\Utility\CsvHelper;

/**
 * The Monthly Income Report — the year's income, how it trended month by month,
 * and what each income account brought in, so the year can be read at a glance
 * and then traced back to the account it came from.
 */
class MonthlyIncome extends AdminComponent
{
    use ExportsReportCsv;

    /** @var int|string */
    public $year = '';

    /** @var IncomeReportQuery */
    protected $income;

    public function boot(IncomeReportQuery $income)
    {
        $this->income = $income;
    }

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    protected function reportKey(): string
    {
        return 'monthly-income';
    }

    protected function exportCsv(): string
    {
        $report = $this->income->monthly((int) $this->year);

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

        return view('livewire.admin.reports.monthly-income', [
            'report' => $this->income->monthly((int) $this->year),
            'years'  => \App\Utility\PeriodHelper::years([]),
        ]);
    }
}
