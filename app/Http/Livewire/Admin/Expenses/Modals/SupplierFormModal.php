<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Suppliers\CreateSupplier;
use App\Actions\Expenses\Suppliers\UpdateSupplier;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Supplier;
use App\Queries\Expenses\SupplierListQuery;

/**
 * Add / Edit Supplier — one form for the trades and businesses the association
 * buys from: a name and contact notes.
 *
 * Opened client-side by Bootstrap (`#supplierFormModal`): a blank dialog from
 * the page's Add Supplier trigger (`openSupplierFormModal`), a pre-filled one
 * carrying the supplier's id (`loadSupplierFormModal`), both relayed by
 * public/js/modal-loader.js.
 */
class SupplierFormModal extends AdminModalComponent
{
    const MODAL_ID = 'supplierFormModal';

    /** @var bool Editing an existing supplier when true */
    public $isEditing = false;

    /** @var int|null Supplier being edited; null when adding a new one */
    public $editingSupplierId = null;

    /** @var array<string, mixed> The supplier's own columns as form state */
    public $form = [
        'name'        => '',
        'description' => '',
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openSupplierFormModal'  => 'openCreate',
        'loadSupplierFormModal'  => 'openEdit',
        'closeSupplierFormModal' => 'closeModal',
    ];

    /** @var SupplierListQuery */
    protected $suppliers;

    public function boot(SupplierListQuery $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    private function rules(): array
    {
        return [
            'form.name'        => ['required', 'string', 'max:150'],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return ['form.name.required' => 'Give the supplier a name.'];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name'        => 'name',
            'form.description' => 'description',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Supplier::class);

        $this->isEditing = false;
        $this->editingSupplierId = null;
        $this->form = ['name' => '', 'description' => ''];
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Supplier::class);

        $supplier = $this->suppliers->find($id);

        $this->isEditing = true;
        $this->editingSupplierId = $supplier->id;
        $this->form = [
            'name'        => (string) $supplier->name,
            'description' => (string) $supplier->description,
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingSupplierId = null;
        $this->form = ['name' => '', 'description' => ''];
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->isEditing
            ? $this->authorize('update', Supplier::class)
            : $this->authorize('create', Supplier::class);

        $validated = $this->validate($this->rules(), $this->messages());
        $data = $validated['form'];

        if ($this->isEditing) {
            $supplier = app(UpdateSupplier::class)->handle(
                $this->suppliers->find($this->editingSupplierId),
                $data,
                auth('web')->user()
            );

            $this->emit('notify', 'success', 'Supplier ' . $supplier->name . ' updated.');
        } else {
            $supplier = app(CreateSupplier::class)->handle($data, auth('web')->user());

            $this->emit('notify', 'success', 'Supplier ' . $supplier->name . ' added.');
        }

        $this->emit('supplier-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.supplier-form-modal', [
            'editing' => $this->isEditing,
        ]);
    }
}
