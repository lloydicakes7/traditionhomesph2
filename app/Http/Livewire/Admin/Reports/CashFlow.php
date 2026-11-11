<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\FinancialStatementQuery;
use App\Utility\CsvHelper;
use Carbon\Carbon;

/**
 * The Cash Flow Statement — what came in, what went out, and what the
 * association's cash and bank accounts hold at the end of the period, account
 * by account.
 */
class CashFlow extends AdminComponent
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
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    protected function reportKey(): string
    {
        return 'cash-flow';
    }

    protected function exportCsv(): string
    {
        $report = $this->statements->cashFlow($this->from, $this->to);

        $rows = array_map(function (array $row) {
            return [
                $row['account']->label,
                number_format($row['opening'], 2, '.', ''),
                number_format($row['in'], 2, '.', ''),
                number_format($row['out'], 2, '.', ''),
                number_format($row['closing'], 2, '.', ''),
            ];
        }, $report['rows']);

        $rows[] = [
            'TOTAL',
            number_format($report['opening'], 2, '.', ''),
            number_format($report['total_in'], 2, '.', ''),
            number_format($report['total_out'], 2, '.', ''),
            number_format($report['closing'], 2, '.', ''),
        ];

        return CsvHelper::build(['Account', 'Opening', 'Money In', 'Money Out', 'Closing'], $rows);
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.cash-flow', [
            'report' => $this->statements->cashFlow($this->from, $this->to),
            'periodLabel' => $this->from && $this->to
                ? Carbon::parse($this->from)->format('M j, Y') . ' – ' . Carbon::parse($this->to)->format('M j, Y')
                : 'All entries posted to date',
        ]);
    }
}
