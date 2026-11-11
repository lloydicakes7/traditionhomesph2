<?php

namespace App\Http\Livewire\Admin\Collections\Products\Modals;

use App\Actions\Collections\Products\DeleteProducts;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\Product;

/**
 * Delete Selected Products or Services — the confirmation the list's
 * mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class ProductMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'productMassDeleteModal';

    /** @var array Item ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadProductMassDeleteModal'  => 'load',
        'closeProductMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteProducts */
    protected $deleteProducts;

    public function boot(DeleteProducts $deleteProducts)
    {
        $this->deleteProducts = $deleteProducts;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Product::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'items';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteProducts->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'product-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.products.modals.product-mass-delete-modal');
    }
}
