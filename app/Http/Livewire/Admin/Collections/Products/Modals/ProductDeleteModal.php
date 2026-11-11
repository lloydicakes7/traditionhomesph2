<?php

namespace App\Http\Livewire\Admin\Collections\Products\Modals;

use App\Actions\Collections\Products\DeleteProducts;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\Product;
use App\Queries\Collections\Products\ProductListQuery;

/**
 * Delete Product or Service — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#productDeleteModal`) with the item's id on
 * the trigger, and loaded lazily by public/js/modal-loader.js. Bill lines
 * already billed from the item keep their own copy of the name and the price
 * they were charged at, so removing it never rewrites what was billed.
 */
class ProductDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'productDeleteModal';

    /** @var int|null Item the confirmation applies to */
    public $deletingProductId = null;

    /** @var string Name the confirmation names */
    public $deletingProductLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadProductDeleteModal'  => 'load',
        'closeProductDeleteModal' => 'closeModal',
    ];

    /** @var ProductListQuery */
    protected $query;

    /** @var DeleteProducts */
    protected $deleteProducts;

    public function boot(ProductListQuery $query, DeleteProducts $deleteProducts)
    {
        $this->query = $query;
        $this->deleteProducts = $deleteProducts;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Product::class);

        $product = $this->query->find($id);

        $this->deletingProductId = $product->id;
        $this->deletingProductLabel = $product->name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingProductId = null;
        $this->deletingProductLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingProductId) {
            return;
        }

        $this->authorize('delete', Product::class);

        $this->deleteProducts->handle([$this->deletingProductId], auth('web')->user());

        $this->emit('notify', 'success', 'Item deleted.');
        $this->emit('product-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.products.modals.product-delete-modal');
    }
}
