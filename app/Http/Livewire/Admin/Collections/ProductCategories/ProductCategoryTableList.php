<?php

namespace App\Http\Livewire\Admin\Collections\ProductCategories;

use App\Http\Livewire\AdminComponent;
use App\Models\Product\ProductCategory;
use App\Queries\Collections\ProductCategories\ProductCategoryListQuery;
use Livewire\WithPagination;

/**
 * Product Categories page — the groups the price list is filed under.
 *
 * A category is only a label, so the page is short: search, add, edit, remove.
 * Removing one never removes the items filed under it — they simply lose their
 * filing, which the foreign key already handles — so nothing billable
 * disappears from the price list because a category was dropped.
 *
 * Split (one folder per page):
 *   ProductCategoryTableList — this component (list state, row actions)
 *   Modals\*                 — the page's modals as event-driven siblings
 */
class ProductCategoryTableList extends AdminComponent
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
        'product-category-saved' => 'refreshList',
    ];

    /** @var ProductCategoryListQuery */
    protected $query;

    public function boot(ProductCategoryListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current search.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids(['search' => $this->search]);
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * A dialog wrote a category — including the mass-delete dialog. Drop the
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
        $this->authorize('viewAny', ProductCategory::class);

        return view('livewire.admin.collections.product-categories.product-category-table-list', [
            'categories' => $this->query->paginate(['search' => $this->search]),
        ]);
    }
}
