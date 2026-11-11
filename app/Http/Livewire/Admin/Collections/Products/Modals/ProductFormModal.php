<?php

namespace App\Http\Livewire\Admin\Collections\Products\Modals;

use App\Actions\Collections\Products\CreateProduct;
use App\Actions\Collections\Products\UpdateProduct;
use App\Enums\ProductType;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Product\Product;
use App\Queries\Collections\Products\ProductListQuery;
use Illuminate\Validation\Rule;

/**
 * Add / Edit a product or service.
 *
 * Opened client-side by Bootstrap (`#productFormModal`) with no id for a new
 * item and with the item's id on the trigger for an edit, and loaded lazily by
 * public/js/modal-loader.js.
 *
 * One form, two ways in: from the Products and Services page, and from the
 * "+ New" link inside an invoice — which is why the saved item is emitted as
 * `product-saved` with its id. The invoice form listens for that and adds the
 * new item as a line, so an officer never leaves the invoice to set up
 * something they are billing right now.
 */
class ProductFormModal extends AdminModalComponent
{
    const MODAL_ID = 'productFormModal';

    /** @var int|null Item being edited; null when adding */
    public $productId = null;

    /** @var array<string, mixed> */
    public $form = [
        'name'                => '',
        'type'                => ProductType::PRODUCT,
        'product_category_id' => '',
        'price'               => 0,
        'unit'                => '',
        'description'         => '',
        'is_active'           => true,
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openProductFormModal'  => 'openCreate',
        'loadProductFormModal'  => 'openEdit',
        'closeProductFormModal' => 'closeModal',
    ];

    /** @var ProductListQuery */
    protected $query;

    /** @var CreateProduct */
    protected $createProduct;

    /** @var UpdateProduct */
    protected $updateProduct;

    public function boot(ProductListQuery $query, CreateProduct $createProduct, UpdateProduct $updateProduct)
    {
        $this->query = $query;
        $this->createProduct = $createProduct;
        $this->updateProduct = $updateProduct;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when these run
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Product::class);

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

        $this->authorize('update', Product::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->productId = $id;

        $product = $this->query->find($this->productId);

        $this->form = [
            'name'                => (string) $product->name,
            'type'                => (string) $product->type,
            'product_category_id' => (string) ($product->product_category_id ?: ''),
            'price'               => (float) $product->price,
            'unit'                => (string) $product->unit,
            'description'         => (string) $product->description,
            'is_active'           => (bool) $product->is_active,
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
        $this->productId = null;
        $this->form = [
            'name'                => '',
            'type'                => ProductType::PRODUCT,
            'product_category_id' => '',
            'price'               => 0,
            'unit'                => '',
            'description'         => '',
            'is_active'           => true,
        ];
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->productId
            ? $this->authorize('update', Product::class)
            : $this->authorize('create', Product::class);

        $this->validate($this->rules(), $this->messages());

        $payload = [
            'name'                => $this->form['name'],
            'type'                => $this->form['type'],
            'product_category_id' => $this->form['product_category_id'] ?: null,
            'price'               => $this->form['price'] ?: 0,
            'unit'                => $this->form['unit'] ?: null,
            'description'         => $this->form['description'] ?: null,
            'is_active'           => (bool) $this->form['is_active'],
        ];

        if ($this->productId) {
            $product = $this->updateProduct->handle(
                $this->query->find($this->productId),
                $payload,
                auth('web')->user()
            );

            $this->emit('notify', 'success', '"' . $product->name . '" updated.');
        } else {
            $product = $this->createProduct->handle($payload, auth('web')->user());

            $this->emit('notify', 'success', '"' . $product->name . '" added.');
        }

        $this->emit('product-saved', ['id' => $product->id]);

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'form.name'                => ['required', 'string', 'max:255'],
            'form.type'                => ['required', Rule::in(ProductType::values())],
            'form.product_category_id' => ['nullable', 'exists:product_categories,id'],
            'form.price'               => ['required', 'numeric', 'min:0'],
            'form.unit'                => ['nullable', 'string', 'max:50'],
            'form.description'         => ['nullable', 'string', 'max:1000'],
            'form.is_active'           => ['boolean'],
        ];
    }

    /**
     * Short, distinct messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.name.required'  => 'Give the item a name.',
            'form.price.required' => 'Enter what the item costs.',
            'form.price.min'      => 'The price cannot be negative.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'form.name'                => 'name',
            'form.type'                => 'type',
            'form.product_category_id' => 'category',
            'form.price'               => 'price',
            'form.unit'                => 'unit',
            'form.description'         => 'description',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // Categories are read only while the form is on screen — the shell's
        // first render still compiles the hidden content, so it is handed empty
        // lists until the record has been loaded.
        if (! $this->loaded) {
            return view('livewire.admin.collections.products.modals.product-form-modal', [
                'types'      => [],
                'categories' => [],
            ]);
        }

        return view('livewire.admin.collections.products.modals.product-form-modal', [
            'types'      => ProductType::labels(),
            'categories' => $this->query->categoryOptions(),
        ]);
    }
}
