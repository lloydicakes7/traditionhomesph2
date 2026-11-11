<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues;

use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\AdminComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\MonthlyDueMatrixQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * Monthly Dues Table View — one year of dues, read as a grid: every property
 * down the side, every month across the top.
 *
 * The dues list answers "what was billed for this month?"; this page answers
 * "how has this property stood across the year?" — the same records, read the
 * other way, which is why it reads through the same query.
 */
class MonthlyDueTableMatrix extends AdminComponent
{
    use WithCsvExport,
        WithPagination;

    /** @var string The year being read, "YYYY" */
    public $year = '';

    /** @var MonthlyDueMatrixQuery */
    protected $query;

    public function boot(MonthlyDueMatrixQuery $query)
    {
        $this->query = $query;
    }

    public function mount(): void
    {
        $this->year = now()->format('Y');
    }

    /**
     * A new year is a new grid — start it on its first page.
     */
    public function updatingYear(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAbility(): string
    {
        return 'exportMonthlyDues';
    }

    protected function exportAuthorizable(): string
    {
        return Bill::class;
    }

    protected function exportCsv(): string
    {
        $matrix = $this->query->report($this->year);

        $columns = array_merge(
            ['Property', 'Owner'],
            array_column($matrix['months'], 'label'),
            ['Year Collected', 'Year Balance']
        );

        $rows = [];

        foreach ($matrix['rows'] as $row) {
            $values = [
                $row['property']->block_lot_label,
                $row['property']->owner?->name ?: '—',
            ];

            foreach ($matrix['months'] as $month) {
                $due = $row['cells'][$month['value']] ?? null;

                $values[] = $due ? $due->total_display . ' / ' . $due->balance_display : '';
            }

            $values[] = $row['row_collected'];
            $values[] = $row['row_balance'];

            $rows[] = $values;
        }

        return CsvHelper::build($columns, $rows);
    }

    protected function exportFileName(): string
    {
        return 'monthly-dues-table-export-' . now()->format('Y-m-d') . '.csv';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        $matrix = $this->query->matrix($this->year, $this->page);

        return view('livewire.admin.collections.monthly-dues.monthly-due-table-matrix', [
            'matrix' => $matrix,
            'years'  => $matrix['year_options'],
        ]);
    }
}
