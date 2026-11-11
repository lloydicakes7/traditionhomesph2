<?php

namespace App\Http\Livewire\Admin\Collections\Invoices\Modals;

use App\Actions\Collections\Invoices\DeleteInvoices;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;

/**
 * Delete Selected Invoices — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class InvoiceMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'invoiceMassDeleteModal';

    /** @var array Bill ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadInvoiceMassDeleteModal'  => 'load',
        'closeInvoiceMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteInvoices */
    protected $deleteInvoices;

    public function boot(DeleteInvoices $deleteInvoices)
    {
        $this->deleteInvoices = $deleteInvoices;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAbility(): string
    {
        return 'deleteInvoice';
    }

    protected function massDeleteAuthorizable(): string
    {
        return Bill::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'invoices';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteInvoices->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'invoice-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.invoices.modals.invoice-mass-delete-modal');
    }
}
