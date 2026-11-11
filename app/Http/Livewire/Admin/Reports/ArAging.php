<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Enums\AgingBucket;
use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\AgingReportQuery;
use App\Utility\CsvHelper;

/**
 * The AR Aging Report — the unpaid homeowner invoices and dues the association
 * has not collected, organized by how long each has gone uncollected: not yet
 * due, one to thirty days, thirty-one to sixty, sixty-one to ninety, and ninety
 * days and over.
 *
 * Both kinds of bill are in it, because both are money the association is owed
 * — a month's dues and a one-off charge age the same way — and the office can
 * narrow the list to one kind, to a block, or to a unit.
 */
class ArAging extends AdminComponent
{
    use ExportsReportCsv;

    /** @var string */
    public $asOf = '';

    /** @var string */
    public $search = '';

    /** @var string */
    public $block = '';

    /** @var string */
    public $lot = '';

    /** @var bool */
    public $includeDues = true;

    /** @var bool */
    public $includeInvoices = true;

    /** @var AgingReportQuery */
    protected $aging;

    public function boot(AgingReportQuery $aging)
    {
        $this->aging = $aging;
    }

    public function mount(): void
    {
        $this->asOf = now()->toDateString();
    }

    /**
     * The filters the report reads.
     *
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return [
            'search'           => $this->search,
            'block'            => $this->block,
            'lot'              => $this->lot,
            'include_dues'     => (bool) $this->includeDues,
            'include_invoices' => (bool) $this->includeInvoices,
        ];
    }

    protected function reportKey(): string
    {
        return 'ar-aging';
    }

    protected function exportCsv(): string
    {
        $report = $this->aging->receivable($this->asOf, $this->filters());

        $rows = array_map(function (array $row) {
            return array_merge(
                [
                    $row['property'] ? $row['property']->block_lot_label : '—',
                    $row['homeowner'],
                    $row['bills'],
                    $row['oldest_days'],
                ],
                array_map(function ($amount) {
                    return number_format($amount, 2, '.', '');
                }, array_values($row['buckets'])),
                [number_format($row['total'], 2, '.', '')]
            );
        }, $report['rows']);

        $buckets = array_map(function (AgingBucket $bucket) {
            return $bucket->label();
        }, $report['buckets']);

        return CsvHelper::build(
            array_merge(['Property', 'Homeowner', 'Uncollected Bills', 'Oldest (days)'], $buckets, ['Total']),
            $rows
        );
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.ar-aging', [
            'report' => $this->aging->receivable($this->asOf, $this->filters()),
        ]);
    }
}
