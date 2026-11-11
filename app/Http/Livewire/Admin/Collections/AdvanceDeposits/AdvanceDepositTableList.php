<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits;

use App\Enums\AdvanceDepositStatus;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;
use App\Utility\MoneyHelper;
use Livewire\WithPagination;

/**
 * Advance Deposits page — the credit homeowners have paid ahead of a bill.
 *
 * Credit is settled against a bill from here (or, a month at a time, from the
 * Monthly Dues Generator), and whatever is not drawn down is returned when the
 * office refunds the deposit.
 *
 * The form, apply, view, delete, refund and mass-delete dialogs are all
 * siblings, each opening its own shell and loading what it shows; the
 * mass-delete bar hands its selection over as the loader's `data-id`, so the
 * page spends no round trip opening it.
 *
 * Split (one folder per page):
 *   AdvanceDepositTableList — this component (list state, row actions)
 *   Modals\*                — the page's modals as event-driven siblings
 */
class AdvanceDepositTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithListCriteria;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Sort field */
    public $sortField = 'deposited_at';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected deposit IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterStatus', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'advance-deposit-saved' => 'refreshList',
        'payment-recorded'      => 'refreshList',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    public function boot(AdvanceDepositListQuery $query)
    {
        $this->query = $query;
    }

    public function getDepositsProperty()
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
     * A dialog wrote a deposit — including the mass-delete dialog. Drop the
     * selection it consumed.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', AdvanceDeposit::class);

        return view('livewire.admin.collections.advance-deposits.advance-deposit-table-list', [
            'deposits'    => $this->deposits,
            'statuses'    => AdvanceDepositStatus::labels(),
            'totalCredit' => MoneyHelper::format(
                AdvanceDeposit::query()->open()->sum('balance')
            ),
        ]);
    }
}
