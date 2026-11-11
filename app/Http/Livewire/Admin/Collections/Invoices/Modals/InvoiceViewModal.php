<?php

namespace App\Http\Livewire\Admin\Collections\Invoices\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\Invoices\InvoiceDetailQuery;

/**
 * View Bill Details — the whole invoice, read-only: who it is issued to,
 * where the unit is, every line, the totals, and the payments recorded against
 * it.
 *
 * Opened client-side by Bootstrap (`#invoiceViewModal`) with the invoice's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. The record is
 * read on every render rather than held as a public property, so a payment
 * recorded from the row behind the open dialog is reflected in it.
 */
class InvoiceViewModal extends AdminModalComponent
{
    /** @var int|null Bill being shown */
    public $invoiceId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadInvoiceViewModal'  => 'load',
        'closeInvoiceViewModal' => 'closeModal',
    ];

    /** @var InvoiceDetailQuery */
    protected $query;

    public function boot(InvoiceDetailQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when this runs
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAnyInvoices', Bill::class);

        $this->invoiceId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->invoiceId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.invoices.modals.invoice-view-modal', [
            'invoice' => $this->invoiceId
                ? $this->query->find($this->invoiceId)
                : null,
        ]);
    }
}
