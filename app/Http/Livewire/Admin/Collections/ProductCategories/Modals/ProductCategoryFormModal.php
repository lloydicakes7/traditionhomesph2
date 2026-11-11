<?php

namespace App\Http\Livewire\Admin\Collections\ProductCategories\Modals;

use App\Actions\Collections\ProductCategories\CreateProductCategory;
use App\Actions\Collections\ProductCategories\UpdateProductCategory;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\ProductCategory;
use App\Queries\Collections\ProductCategories\ProductCategoryListQuery;

/**
 * Add / Edit a product category — a name and an optional note.
 *
 * Opened client-side by Bootstrap (`#productCategoryFormModal`) with no id for
 * a new category and with the category's id on the trigger for an edit, and
 * loaded lazily by public/js/modal-loader.js.
 */
class ProductCategoryFormModal extends AdminModalComponent
{
    const MODAL_ID = 'productCategoryFormModal';

    /** @var int|null Category being edited; null when adding */
    public $categoryId = null;

    /** @var array<string, mixed> */
    public $form = [
        'name'        => '',
        'description' => '',
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openProductCategoryFormModal'  => 'openCreate',
        'loadProductCategoryFormModal'  => 'openEdit',
        'closeProductCategoryFormModal' => 'closeModal',
    ];

    /** @var ProductCategoryListQuery */
    protected $query;

    /** @var CreateProductCategory */
    protected $createCategory;

    /** @var UpdateProductCategory */
    protected $updateCategory;

    public function boot(
        ProductCategoryListQuery $query,
        CreateProductCategory $createCategory,
        UpdateProductCategory $updateCategory
    ) {
        $this->query = $query;
        $this->createCategory = $createCategory;
        $this->updateCategory = $updateCategory;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when these run
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', ProductCategory::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * @param  int|null  $id
     */
    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', ProductCategory::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->categoryId = $id;

        $category = $this->query->find($this->categoryId);

        $this->form = [
            'name'        => (string) $category->name,
            'description' => (string) $category->description,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetModalState(): void
    {
        $this->categoryId = null;
        $this->form = ['name' => '', 'description' => ''];
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->categoryId
            ? $this->authorize('update', ProductCategory::class)
            : $this->authorize('create', ProductCategory::class);

        $this->validate([
            'form.name'        => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ], [
            'form.name.required' => 'Give the category a name.',
        ], [
            'form.name'        => 'name',
            'form.description' => 'description',
        ]);

        $payload = [
            'name'        => $this->form['name'],
            'description' => $this->form['description'] ?: null,
        ];

        if ($this->categoryId) {
            $category = $this->updateCategory->handle(
                $this->query->find($this->categoryId),
                $payload,
                auth('web')->user()
            );

            $this->emit('notify', 'success', '"' . $category->name . '" updated.');
        } else {
            $category = $this->createCategory->handle($payload, auth('web')->user());

            $this->emit('notify', 'success', '"' . $category->name . '" added.');
        }

        $this->emit('product-category-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.product-categories.modals.product-category-form-modal');
    }
}
