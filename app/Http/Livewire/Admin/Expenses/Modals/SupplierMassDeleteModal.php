<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Suppliers\DeleteSuppliers;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

/**
 * Delete Selected Suppliers — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class SupplierMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete {
        deleteMass as protected deleteMassSelection;
    }

    const MODAL_ID = 'supplierMassDeleteModal';

    /** @var array Supplier ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadSupplierMassDeleteModal'  => 'load',
        'closeSupplierMassDeleteModal' => 'closeModal',
    ];

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
        return Supplier::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'suppliers';
    }

    protected function massDeleteIds(array $ids): int
    {
        return app(DeleteSuppliers::class)->handle($ids, auth('web')->user());
    }

    public function deleteMass(): void
    {
        try {
            $this->deleteMassSelection();
        } catch (ValidationException $exception) {
            $this->addError('massDelete', collect($exception->errors())->flatten()->first());
        }
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'supplier-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.supplier-mass-delete-modal');
    }
}
