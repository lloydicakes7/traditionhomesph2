<?php

namespace App\Http\Livewire\Admin\Expenses;

use App\Http\Livewire\AdminComponent;
use App\Models\Expense\ExpenseCategory;
use App\Queries\Expenses\ExpenseCategoryListQuery;
use Livewire\WithPagination;

/**
 * Expense Categories page — the office's own filing labels: search, pagination,
 * selection, and the row actions.
 *
 * The Add and Edit actions are `data-toggle` triggers for the form dialog; the
 * Delete action likewise opens its own confirmation. Mass-delete is one of those
 * triggers, carrying the selection as the loader's `data-id`, so the page holds
 * only the list's own state.
 *
 * Split (one folder per page):
 *   CategoryTableList — this component (list state, row actions)
 *   Modals\*          — the page's modals as event-driven siblings
 */
class CategoryTableList extends AdminComponent
{
    use WithPagination;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var array Selected category IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'expense-category-saved' => 'refreshList',
    ];

    /** @var ExpenseCategoryListQuery */
    protected $categories;

    public function boot(ExpenseCategoryListQuery $categories)
    {
        $this->categories = $categories;
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current search.
     */
    public function resolveSelectedIds(): array
    {
        return $this->categories->ids(['search' => $this->search]);
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * One of the page's dialogs wrote a category — including the mass-delete
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
        $this->authorize('viewAny', ExpenseCategory::class);

        return view('livewire.admin.expenses.category-table-list', [
            'categories' => $this->categories->paginate(['search' => $this->search]),
        ]);
    }
}
