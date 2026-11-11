<?php

namespace App\Http\Livewire\Admin\Collections\ProductCategories\Modals;

use App\Actions\Collections\ProductCategories\DeleteProductCategories;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\ProductCategory;
use App\Queries\Collections\ProductCategories\ProductCategoryListQuery;

/**
 * Delete Product Category — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#productCategoryDeleteModal`) with the
 * category's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * Removing a category never removes the items filed under it — they simply
 * become uncategorised, which the foreign key already handles.
 */
class ProductCategoryDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'productCategoryDeleteModal';

    /** @var int|null Category the confirmation applies to */
    public $deletingCategoryId = null;

    /** @var string Name the confirmation names */
    public $deletingCategoryLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadProductCategoryDeleteModal'  => 'load',
        'closeProductCategoryDeleteModal' => 'closeModal',
    ];

    /** @var ProductCategoryListQuery */
    protected $query;

    /** @var DeleteProductCategories */
    protected $deleteCategories;

    public function boot(ProductCategoryListQuery $query, DeleteProductCategories $deleteCategories)
    {
        $this->query = $query;
        $this->deleteCategories = $deleteCategories;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', ProductCategory::class);

        $category = $this->query->find($id);

        $this->deletingCategoryId = $category->id;
        $this->deletingCategoryLabel = $category->name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingCategoryId = null;
        $this->deletingCategoryLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingCategoryId) {
            return;
        }

        $this->authorize('delete', ProductCategory::class);

        $this->deleteCategories->handle([$this->deletingCategoryId], auth('web')->user());

        $this->emit('notify', 'success', 'Category deleted. Its items are now uncategorised.');
        $this->emit('product-category-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.product-categories.modals.product-category-delete-modal');
    }
}
