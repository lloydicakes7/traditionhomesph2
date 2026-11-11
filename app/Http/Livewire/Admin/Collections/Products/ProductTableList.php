<?php

namespace App\Http\Livewire\Admin\Collections\Products;

use App\Actions\Collections\Products\ToggleProductActive;
use App\Enums\ProductType;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Product\Product;
use App\Queries\Collections\Products\ProductListQuery;
use Livewire\WithPagination;

/**
 * Products and Services page — what the association bills for outside the
 * monthly dues: vehicle stickers, ID cards, garbage fees, clearance fees,
 * special assessments.
 *
 * Every one-off invoice line comes from this list, so the page is also what the
 * Add Invoice form's "+ New" link opens: the form is the same component either
 * way, and this page's list simply refreshes when an item is saved elsewhere
 * (the `product-saved` listener).
 *
 * Split (one folder per page):
 *   ProductTableList — this component (list state, row actions)
 *   Modals\*         — the page's modals as event-driven siblings
 */
class ProductTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithListCriteria;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Product/Service filter */
    public $filterType = '';

    /** @var string Category filter */
    public $filterCategory = '';

    /** @var string Sort field */
    public $sortField = 'name';

    /** @var string Sort direction */
    public $sortDirection = 'asc';

    /** @var array Selected product IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterType', 'filterCategory', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'product-saved' => 'refreshList',
    ];

    /** @var ProductListQuery */
    protected $query;

    /** @var ToggleProductActive */
    protected $toggleProduct;

    public function boot(ProductListQuery $query, ToggleProductActive $toggleProduct)
    {
        $this->query = $query;
        $this->toggleProduct = $toggleProduct;
    }

    public function getProductsProperty()
    {
        return $this->query->paginate($this->listCriteria());
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
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
     * A dialog wrote an item — including the mass-delete dialog. Drop the
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
     * Switch the item on or off the price list. Bill lines already billed
     * from it keep the name and price they carry.
     */
    public function toggleActive($id): void
    {
        $this->authorize('update', Product::class);

        $active = $this->toggleProduct->handle($this->query->find((int) $id), auth('web')->user());

        $this->emit('notify', 'success', $active
            ? 'Item is now available to bill.'
            : 'Item is no longer available to bill. Lines already issued keep it.');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Product::class);

        return view('livewire.admin.collections.products.product-table-list', [
            'products'   => $this->products,
            'types'      => ProductType::labels(),
            'categories' => $this->query->categoryOptions(),
        ]);
    }
}
