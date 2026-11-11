<?php

namespace App\Http\Livewire\Admin\Support\Violations;

use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Violation;
use App\Queries\Support\Violations\ViolationListQuery;
use Livewire\WithPagination;

/**
 * Violations page — the association's citations against homeowners: search, the
 * type and status filters, sorting, pagination, selection, and the row actions.
 *
 * A row's moves are Bootstrap triggers: each opens one shared dialog per page
 * per action by id (`#violationViewModal`, `#violationFormModal`,
 * `#violationResolveModal`, `#violationDeleteModal`), and
 * `public/js/modal-loader.js` hands the row's id to that dialog, which loads
 * the record itself.
 *
 * Resolving is the move that matters here, and it is not a deletion: it closes
 * the violation, which is what takes it off the member's current list and
 * leaves it in their history. Whether that move is still open is the record's
 * own call (`can_be_resolved`, from ViolationStatus), never re-derived from a
 * list of statuses in this component.
 *
 * Split (one folder per page):
 *   ViolationTableList — this component (list state, row actions)
 *   Modals\*           — every modal as its own event-driven component
 */
class ViolationTableList extends AdminComponent
{
    use WithPagination,
        WithSorting;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Violation type filter */
    public $filterType = '';

    /** @var string Sort field */
    public $sortField = 'occurred_on';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected violation IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterStatus', 'filterType', 'sortField', 'sortDirection',
    ];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'violation-saved' => 'refreshList',
    ];

    /** @var ViolationListQuery */
    protected $list;

    public function boot(ViolationListQuery $list)
    {
        $this->list = $list;
    }

    // =====================================================================
    // Filters — every one of them returns the table to its first page
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
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
        return $this->list->ids($this->criteria());
    }

    /**
     * Toggle "all pages" using the filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * Another component wrote a violation — a save, a resolve, a delete, a mass
     * delete.
     *
     * Drop the selection it consumed, so a bulk delete never leaves the toolbar
     * standing over rows that are already gone, and let the re-render that
     * follows this call load what is on file now.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'filters'   => ['status' => $this->filterStatus, 'type' => $this->filterType],
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Violation::class);

        return view('livewire.admin.support.violations.violation-table-list', [
            'violations' => $this->list->paginate($this->criteria()),
            'statuses' => ViolationStatus::labels(),
            'types'    => ViolationType::labels(),
        ]);
    }
}
