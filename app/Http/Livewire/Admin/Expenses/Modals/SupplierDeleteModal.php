<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Suppliers\DeleteSuppliers;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Supplier;
use App\Queries\Expenses\SupplierListQuery;
use Illuminate\Validation\ValidationException;

/**
 * Delete Supplier — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#supplierDeleteModal`) with the supplier's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. A supplier
 * that expenses already name cannot be removed, since there is no disable to
 * fall back on.
 */
class SupplierDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'supplierDeleteModal';

    /** @var int|null Supplier the confirmation applies to */
    public $deletingSupplierId = null;

    /** @var string Name of the supplier the confirmation names */
    public $deletingSupplierLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadSupplierDeleteModal'  => 'load',
        'closeSupplierDeleteModal' => 'closeModal',
    ];

    /** @var SupplierListQuery */
    protected $suppliers;

    public function boot(SupplierListQuery $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Supplier::class);

        $supplier = $this->suppliers->find($id);

        $this->deletingSupplierId = $supplier->id;
        $this->deletingSupplierLabel = $supplier->name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingSupplierId = null;
        $this->deletingSupplierLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingSupplierId) {
            return;
        }

        $this->authorize('delete', Supplier::class);

        try {
            app(DeleteSuppliers::class)->handle([$this->deletingSupplierId], auth('web')->user());
        } catch (ValidationException $exception) {
            $this->addError('delete', collect($exception->errors())->flatten()->first());

            return;
        }

        $this->emit('notify', 'success', 'Supplier deleted.');
        $this->emit('supplier-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.supplier-delete-modal');
    }
}
