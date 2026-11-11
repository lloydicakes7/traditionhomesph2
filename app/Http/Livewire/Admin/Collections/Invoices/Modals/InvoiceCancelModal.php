<?php

namespace App\Http\Livewire\Admin\Collections\Invoices\Modals;

use App\Actions\Collections\Invoices\CancelInvoice;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\Invoices\InvoiceDetailQuery;

/**
 * Cancel Bill — the confirmation a row's Cancel action opens.
 *
 * Opened client-side by Bootstrap (`#invoiceCancelModal`) with the invoice's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Withdrawing an
 * invoice stops it being collectible; the payments already recorded stay on the
 * ledger, which is why this is not a delete.
 */
class InvoiceCancelModal extends AdminModalComponent
{
    const MODAL_ID = 'invoiceCancelModal';

    /** @var int|null Bill the confirmation applies to */
    public $cancellingInvoiceId = null;

    /** @var string Reference of the invoice the confirmation names */
    public $cancellingInvoiceLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadInvoiceCancelModal'  => 'load',
        'closeInvoiceCancelModal' => 'closeModal',
    ];

    /** @var InvoiceDetailQuery */
    protected $query;

    /** @var CancelInvoice */
    protected $cancelInvoice;

    public function boot(InvoiceDetailQuery $query, CancelInvoice $cancelInvoice)
    {
        $this->query = $query;
        $this->cancelInvoice = $cancelInvoice;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('updateInvoice', Bill::class);

        $invoice = $this->query->findForEdit($id);

        $this->cancellingInvoiceId = $invoice->id;
        $this->cancellingInvoiceLabel = $invoice->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->cancellingInvoiceId = null;
        $this->cancellingInvoiceLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Cancel
    // =====================================================================

    public function cancel(): void
    {
        if (! $this->loaded || ! $this->cancellingInvoiceId) {
            return;
        }

        $this->authorize('updateInvoice', Bill::class);

        $invoice = $this->cancelInvoice->handle(
            $this->query->findForEdit($this->cancellingInvoiceId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Bill ' . $invoice->reference . ' cancelled.');
        $this->emit('invoice-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.invoices.modals.invoice-cancel-modal');
    }
}
