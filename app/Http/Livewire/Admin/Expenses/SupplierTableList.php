<?php

namespace App\Http\Livewire\Admin\Expenses;

use App\Http\Livewire\AdminComponent;
use App\Models\Supplier;
use App\Queries\Expenses\SupplierListQuery;
use Livewire\WithPagination;

/**
 * Suppliers page — the trades and businesses the association buys from: search,
 * pagination, selection, and the row actions.
 *
 * The Add and Edit actions are `data-toggle` triggers for the form dialog; the
 * Delete action likewise opens its own confirmation. Mass-delete is one of those
 * triggers, carrying the selection as the loader's `data-id`, so the page holds
 * only the list's own state.
 *
 * Split (one folder per page):
 *   SupplierTableList — this component (list state, row actions)
 *   Modals\*          — the page's modals as event-driven siblings
 */
class SupplierTableList extends AdminComponent
{
    use WithPagination;

    /** @var string Search query */
    public $search = '';

    /** @var array Selected supplier IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = ['supplier-saved' => 'refreshList'];

    /** @var SupplierListQuery */
    protected $suppliers;

    public function boot(SupplierListQuery $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current search.
     */
    public function resolveSelectedIds(): array
    {
        return $this->suppliers->ids(['search' => $this->search]);
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * One of the page's dialogs wrote a supplier — including the mass-delete
     * dialog. Drop the selection it consumed.
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
        $this->authorize('viewAny', Supplier::class);

        return view('livewire.admin.expenses.supplier-table-list', [
            'suppliers' => $this->suppliers->paginate(['search' => $this->search]),
        ]);
    }
}
