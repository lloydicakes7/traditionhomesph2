<?php

namespace App\Http\Livewire\Admin\Support\Complaints;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Complaint;
use App\Queries\Support\Complaints\ComplaintListQuery;
use Livewire\WithPagination;

class ComplaintTableList extends AdminComponent
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

    /** @var string Category filter */
    public $filterCategory = '';

    /** @var string Sort field */
    public $sortField = 'created_at';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected complaint IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterStatus', 'filterCategory', 'sortField', 'sortDirection',
    ];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'complaint-saved' => 'refreshList',
    ];

    /** @var ComplaintListQuery */
    protected $list;

    public function boot(ComplaintListQuery $list)
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

    public function updatingFilterCategory(): void
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
     * Another component wrote a complaint — a save, a delete, a mass delete.
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
            'filters'   => ['status' => $this->filterStatus, 'category' => $this->filterCategory],
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Complaint::class);

        return view('livewire.admin.support.complaints.complaint-table-list', [
            'complaints' => $this->list->paginate($this->criteria()),
            'statuses'   => ComplaintStatus::labels(),
            'categories' => ComplaintCategory::labels(),
        ]);
    }
}
