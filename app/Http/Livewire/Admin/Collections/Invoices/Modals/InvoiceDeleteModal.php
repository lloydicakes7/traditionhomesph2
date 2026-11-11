<?php

namespace App\Http\Livewire\Admin\Collections\Invoices\Modals;

use App\Actions\Collections\Invoices\DeleteInvoices;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\Invoices\InvoiceDetailQuery;

/**
 * Delete Bill — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#invoiceDeleteModal`) with the invoice's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Money received
 * against a bill is the ledger's to keep, so an invoice that has been paid is
 * refused here and points the officer at Cancel instead.
 */
class InvoiceDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'invoiceDeleteModal';

    /** @var int|null Bill the confirmation applies to */
    public $deletingInvoiceId = null;

    /** @var string Reference of the invoice the confirmation names */
    public $deletingInvoiceLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadInvoiceDeleteModal'  => 'load',
        'closeInvoiceDeleteModal' => 'closeModal',
    ];

    /** @var InvoiceDetailQuery */
    protected $query;

    /** @var DeleteInvoices */
    protected $deleteInvoices;

    public function boot(InvoiceDetailQuery $query, DeleteInvoices $deleteInvoices)
    {
        $this->query = $query;
        $this->deleteInvoices = $deleteInvoices;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('deleteInvoice', Bill::class);

        $invoice = $this->query->findForEdit($id);

        $this->deletingInvoiceId = $invoice->id;
        $this->deletingInvoiceLabel = $invoice->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingInvoiceId = null;
        $this->deletingInvoiceLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingInvoiceId) {
            return;
        }

        $this->authorize('deleteInvoice', Bill::class);

        $deleted = $this->deleteInvoices->handle([$this->deletingInvoiceId], auth('web')->user());

        if ($deleted) {
            $this->emit('notify', 'success', 'Bill deleted.');
        } else {
            $this->emit('notify', 'warning', 'That invoice has payments recorded against it and cannot be deleted. Cancel it instead.');
        }

        $this->emit('invoice-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.invoices.modals.invoice-delete-modal');
    }
}
