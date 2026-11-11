<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\ExpenseReportQuery;
use App\Utility\CsvHelper;

/**
 * The Daily Expenses Report — what a single day spent, by expense account, read
 * from the expenses approved that day so it can be laid beside the day's income.
 */
class DailyExpenses extends AdminComponent
{
    use ExportsReportCsv;

    /** @var string */
    public $date = '';

    /** @var ExpenseReportQuery */
    protected $expenses;

    public function boot(ExpenseReportQuery $expenses)
    {
        $this->expenses = $expenses;
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    protected function reportKey(): string
    {
        return 'daily-expenses';
    }

    protected function exportCsv(): string
    {
        $report = $this->expenses->daily($this->date);

        $rows = array_map(function (array $row) {
            return [
                $row['account']->code,
                $row['account']->name,
                number_format($row['amount'], 2, '.', ''),
            ];
        }, $report['rows']);

        $rows[] = ['', 'TOTAL', number_format($report['total'], 2, '.', '')];

        return CsvHelper::build(['Code', 'Account', 'Amount'], $rows);
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.daily-expense', [
            'report' => $this->expenses->daily($this->date ?: now()->toDateString()),
        ]);
    }
}
