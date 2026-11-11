<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\FinancialStatementQuery;
use App\Utility\CsvHelper;
use Carbon\Carbon;

/**
 * The Profit and Loss Statement — what the association earned over a period,
 * what it spent, and which of the two it came to.
 */
class ProfitAndLoss extends AdminComponent
{
    use ExportsReportCsv;

    /** @var string */
    public $from = '';

    /** @var string */
    public $to = '';

    /** @var FinancialStatementQuery */
    protected $statements;

    public function boot(FinancialStatementQuery $statements)
    {
        $this->statements = $statements;
    }

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    protected function reportKey(): string
    {
        return 'profit-and-loss';
    }

    protected function exportCsv(): string
    {
        $report = $this->statements->profitAndLoss($this->from, $this->to);

        $rows = [];

        foreach ($report['revenue'] as $row) {
            $rows[] = ['Income', $row['account']->code, $row['account']->name, number_format($row['signed'], 2, '.', '')];
        }

        foreach ($report['expenses'] as $row) {
            $rows[] = ['Expense', $row['account']->code, $row['account']->name, number_format($row['signed'], 2, '.', '')];
        }

        $rows[] = ['', '', 'TOTAL INCOME', number_format($report['total_revenue'], 2, '.', '')];
        $rows[] = ['', '', 'TOTAL EXPENSES', number_format($report['total_expenses'], 2, '.', '')];
        $rows[] = ['', '', 'NET INCOME', number_format($report['net_income'], 2, '.', '')];

        return CsvHelper::build(['Group', 'Code', 'Account', 'Amount'], $rows);
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.profit-and-loss', [
            'report'     => $this->statements->profitAndLoss($this->from, $this->to),
            'periodLabel' => $this->from && $this->to
                ? Carbon::parse($this->from)->format('M j, Y') . ' – ' . Carbon::parse($this->to)->format('M j, Y')
                : 'All entries posted to date',
        ]);
    }
}
