<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\CollectionReportQuery;
use App\Utility\CsvHelper;

/**
 * The Daily Collection Report — the day's collection register: every payment
 * received on a date, who paid it and what it settled, and what the day came
 * to. Money drawn from credit a homeowner paid ahead is left out, because it is
 * credit being applied rather than money received.
 */
class DailyCollection extends AdminComponent
{
    use ExportsReportCsv;

    /** @var string */
    public $date = '';

    /** @var CollectionReportQuery */
    protected $collections;

    public function boot(CollectionReportQuery $collections)
    {
        $this->collections = $collections;
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    protected function reportKey(): string
    {
        return 'daily-collection';
    }

    protected function exportCsv(): string
    {
        $report = $this->collections->daily($this->date);

        $rows = array_map(function (array $row) {
            return [
                $row['reference'],
                $row['property'] ? $row['property']->block_lot_label : '—',
                $row['homeowner'],
                $row['particulars'],
                $row['method'],
                number_format($row['amount'], 2, '.', ''),
            ];
        }, $report['rows']);

        $rows[] = ['', '', '', '', 'TOTAL', number_format($report['total'], 2, '.', '')];

        return CsvHelper::build(
            ['Reference', 'Unit', 'Received From', 'Particulars', 'Method', 'Amount'],
            $rows
        );
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.daily-collection', [
            'report' => $this->collections->daily($this->date ?: now()->toDateString()),
        ]);
    }
}
