<?php

namespace App\Http\Livewire\Admin\Collections\ProductCategories\Modals;

use App\Actions\Collections\ProductCategories\DeleteProductCategories;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\ProductCategory;

/**
 * Delete Selected Categories — the confirmation the list's mass-delete bar
 * opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class ProductCategoryMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'productCategoryMassDeleteModal';

    /** @var array Category ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadProductCategoryMassDeleteModal'  => 'load',
        'closeProductCategoryMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteProductCategories */
    protected $deleteCategories;

    public function boot(DeleteProductCategories $deleteCategories)
    {
        $this->deleteCategories = $deleteCategories;
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
        return ProductCategory::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'categories';
    }

    protected function massDeleteIds(array $ids): int
    {
        $this->deleteCategories->handle($ids, auth('web')->user());

        return count($ids);
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'product-category-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.product-categories.modals.product-category-mass-delete-modal');
    }
}
