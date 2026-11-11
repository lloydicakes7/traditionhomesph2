<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\IncomeReportQuery;
use App\Utility\CsvHelper;

/**
 * The Daily Income Report — what a single day earned, by income account, read
 * from the payments recorded that day so it says what the day actually took in.
 */
class DailyIncome extends AdminComponent
{
    use ExportsReportCsv;

    /** @var string */
    public $date = '';

    /** @var IncomeReportQuery */
    protected $income;

    public function boot(IncomeReportQuery $income)
    {
        $this->income = $income;
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    protected function reportKey(): string
    {
        return 'daily-income';
    }

    protected function exportCsv(): string
    {
        $report = $this->income->daily($this->date);

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

        return view('livewire.admin.reports.daily-income', [
            'report' => $this->income->daily($this->date ?: now()->toDateString()),
        ]);
    }
}
