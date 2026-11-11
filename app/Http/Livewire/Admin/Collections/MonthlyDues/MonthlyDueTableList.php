<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues;

use App\Enums\BillStatus;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Queries\Collections\MonthlyDues\MonthlyDueListQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * Monthly Dues Invoice page — the recurring dues, reviewed property by
 * property: reference, property, due month, totals, penalty, discount, paid,
 * balance and status.
 *
 * These rows are not invoices in the Billing & Invoices sense — they are raised
 * a month at a time for every property by the generator, which is why they have
 * their own page, their own list and their own generator.
 *
 * Split (one folder per page):
 *   MonthlyDueTableList — this component (list state, row actions)
 *   Modals\*            — the page's modals as event-driven siblings
 */
class MonthlyDueTableList extends AdminComponent
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
    public $sortField = 'billing_period';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected due IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterStatus', 'filterMonth', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'payment-recorded' => 'refreshList',
        'due-saved'        => 'refreshList',
    ];

    /** @var MonthlyDueListQuery */
    protected $query;

    public function boot(MonthlyDueListQuery $query)
    {
        $this->query = $query;
    }

    public function getDuesProperty()
    {
        return $this->query->paginate($this->listCriteria());
    }

    // =====================================================================
    // Filters
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
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->listCriteria());
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * A dialog wrote a monthly due — including the mass-delete dialog. Drop the
     * selection it consumed.
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
     * Record a receipt against this month's dues. Receiving money is the
     * ledger's own permission, whichever bill it settled, and the dialog needs
     * the bill the row names, so it is opened by the page.
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
        return 'exportMonthlyDues';
    }

    protected function exportAuthorizable(): string
    {
        return Bill::class;
    }

    protected function exportCsv(): string
    {
        $ids = $this->query->ids($this->listCriteria());

        $rows = Bill::with(['user.resident', 'user.household.property'])
            ->whereIn('id', $ids)
            ->ordered()
            ->get()
            ->map(function (Bill $due) {
                return [
                    $due->reference,
                    $due->property?->block_lot_label ?: '—',
                    $due->issued_to_name,
                    $due->billing_period_display,
                    $due->subtotal_display,
                    $due->penalty_display,
                    $due->discount_display,
                    $due->total_display,
                    $due->paid_display,
                    $due->balance_display,
                    $due->status_label,
                ];
            })
            ->all();

        return CsvHelper::build([
            'Reference No.', 'Property', 'Billed To', 'Due Month', 'Amount',
            'Penalty', 'Discount', 'Total', 'Paid', 'Balance', 'Status',
        ], $rows);
    }

    protected function exportFileName(): string
    {
        return 'due-export-' . now()->format('Y-m-d') . '.csv';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        return view('livewire.admin.collections.monthly-dues.monthly-due-table-list', [
            'dues'     => $this->dues,
            'statuses' => BillStatus::labels(),
            'months'   => $this->query->months(),
        ]);
    }
}
