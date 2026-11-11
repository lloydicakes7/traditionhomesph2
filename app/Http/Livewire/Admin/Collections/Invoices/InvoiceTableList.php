<?php

namespace App\Http\Livewire\Admin\Collections\Invoices;

use App\Enums\BillStatus;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Queries\Collections\Invoices\InvoiceListQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * Billing & Invoices page — the one-off charges: search, filters, sorting,
 * pagination, selection, and the row actions.
 *
 * The page's dialogs are siblings, each opening its own shell: the form and the
 * delete/cancel confirmations open client-side from a `data-toggle` trigger and
 * load their record lazily, and the receipt — which needs the bill the row
 * settled — is opened by `recordPayment()` with `loadRecordPaymentModal`.
 * Mass-delete is one of those triggers, carrying the selection as the loader's
 * `data-id`.
 *
 * Monthly dues are not on this page at all — they are their own record with
 * their own generator, which is what the page's own words promise.
 *
 * Split (one folder per page):
 *   InvoiceTableList — this component (list state, row actions)
 *   Modals\*         — the page's modals as event-driven siblings
 */
class InvoiceTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithListCriteria,
        WithCsvExport;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Billing month filter, "Y-m" */
    public $filterMonth = '';

    /** @var string Sort field */
    public $sortField = 'bill_date';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected invoice IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterStatus', 'filterMonth', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'invoice-saved'    => 'refreshList',
        'payment-recorded' => 'refreshList',
    ];

    /** @var InvoiceListQuery */
    protected $query;

    public function boot(InvoiceListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Queries
    // =====================================================================

    public function getInvoicesProperty()
    {
        return $this->query->paginate($this->listCriteria());
    }

    // =====================================================================
    // Filters — every one of them returns the table to its first page
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Selection ("select all pages")
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->listCriteria());
    }

    /**
     * Toggle "all pages" using the service-filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * One of the page's dialogs wrote an invoice — including the mass-delete
     * dialog. Drop the selection it consumed, so a bulk delete never leaves the
     * toolbar standing over rows that are already gone.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    // =====================================================================
    // Row actions needing the server
    // =====================================================================

    /**
     * Record a receipt against this invoice. Receiving money is the ledger's
     * own permission, whichever bill it settled, and the dialog needs the bill
     * the row names, so it is opened by the page.
     */
    public function recordPayment($id): void
    {
        $this->authorize('create', Payment::class);

        $this->emit('loadRecordPaymentModal', ['bill' => (string) (int) $id]);
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAbility(): string
    {
        return 'exportInvoices';
    }

    protected function exportAuthorizable(): string
    {
        return Bill::class;
    }

    protected function exportCsv(): string
    {
        $invoices = $this->query->ids($this->listCriteria());

        $rows = Bill::with(['user.resident', 'user.household.property'])
            ->whereIn('id', $invoices)
            ->ordered()
            ->get()
            ->map(function (Bill $invoice) {
                return [
                    $invoice->bill_date_display,
                    $invoice->reference,
                    $invoice->property?->block_lot_label ?: '—',
                    $invoice->issued_to_name,
                    $invoice->total_display,
                    $invoice->penalty_display,
                    $invoice->discount_display,
                    $invoice->paid_display,
                    $invoice->balance_display,
                    $invoice->status_label,
                ];
            })
            ->all();

        return CsvHelper::build([
            'Bill Date', 'Reference No.', 'Property', 'Billed To',
            'Total', 'Penalty', 'Discount', 'Paid', 'Balance', 'Status',
        ], $rows);
    }

    protected function exportFileName(): string
    {
        return 'invoice-export-' . now()->format('Y-m-d') . '.csv';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAnyInvoices', Bill::class);

        return view('livewire.admin.collections.invoices.invoice-table-list', [
            'invoices' => $this->invoices,
            'statuses' => BillStatus::labels(),
            'months'   => $this->query->months(),
        ]);
    }
}
