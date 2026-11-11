<?php

namespace App\Http\Livewire\Admin\Accounting\Transfers;

use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Account\AccountTransfer;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Queries\Accounting\TransferListQuery;
use App\Utility\CsvHelper;
use App\Utility\PeriodHelper;
use Livewire\WithPagination;

/**
 * The Transfers page — money moved between the association's own accounts.
 *
 * A transfer is not income and not an expense: it is the same money changing
 * pockets, so the register reads as pairs of accounts rather than as bills.
 * Recording one opens the transfer dialog, which posts the balanced entry that
 * actually moves the money; removing one voids that posting, because the books
 * keep saying what was moved.
 *
 * Split (one folder per page):
 *   TransferTableList — this component (list state, row actions)
 *   Modals\*          — the page's modals as event-driven siblings
 */
class TransferTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithCsvExport;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Month filter, "Y-m" */
    public $filterMonth = '';

    /** @var string Account filter — either side of the transfer */
    public $filterAccount = '';

    /** @var string Sort field */
    public $sortField = 'transfer_date';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterMonth', 'filterAccount', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'transfer-saved' => '$refresh',
    ];

    /** @var TransferListQuery */
    protected $transfers;

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(TransferListQuery $transfers, ChartOfAccountsQuery $chart)
    {
        $this->transfers = $transfers;
        $this->chart = $chart;
    }

    /**
     * The filters as the query reads them.
     *
     * @return array<string, mixed>
     */
    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'filters'   => [
                'month'   => $this->filterMonth,
                'account' => $this->filterAccount,
            ],
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
        ];
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAccount(): void
    {
        $this->resetPage();
    }

    /**
     * Drop every filter — the list's reset control.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterMonth = '';
        $this->filterAccount = '';

        $this->resetPage();
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAuthorizable(): string
    {
        return AccountTransfer::class;
    }

    /**
     * The name the download is saved under. Named here rather than derived
     * from a modal prefix, because this page's dialogs name themselves.
     */
    protected function exportFileName(): string
    {
        return 'transfer-export-' . now()->format('Y-m-d') . '.csv';
    }

    protected function exportCsv(): string
    {
        $rows = collect($this->transfers->exportable($this->criteria()))
            ->map(function (AccountTransfer $transfer) {
                return [
                    $transfer->reference,
                    $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : '',
                    $transfer->from_label,
                    $transfer->to_label,
                    $transfer->amount_display,
                    (string) $transfer->memo,
                ];
            })
            ->all();

        return CsvHelper::build(
            ['Reference', 'Date', 'From Account', 'To Account', 'Amount', 'Memo'],
            $rows
        );
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', AccountTransfer::class);

        return view('livewire.admin.accounting.transfers.transfer-table-list', [
            'transfers' => $this->transfers->paginate($this->criteria()),
            'accounts'  => $this->chart->cashOptions(),
            'months'    => PeriodHelper::recentMonths(),
            'totals'    => $this->transfers->totals($this->criteria()),
        ]);
    }
}
