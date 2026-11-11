<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Actions\Collections\Payments\ApprovePayment;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;
use App\Models\Request;
use App\Queries\Collections\Payments\PaymentDetailQuery;
use App\Queries\Support\Requests\RequestDetailQuery;

/**
 * "Request Payments" modal — the invoice a request carries and the receipts
 * filed against it, so the office can see whether the homeowner has paid and
 * record a waiting portal payment without leaving the Requests page.
 *
 * The office hands a request's document over only once its invoice is settled,
 * and a payment filed from the portal — while that flow still exists in old
 * records — waits as pending until an officer records it (see
 * \App\Enums\PaymentStatus). That act belongs on the ledger's own Payments page,
 * but the officer tracking a request should not have to leave it: this dialog
 * reads the invoice's receipts through the ledger's own query and approves a
 * waiting one with the same call the Payments page uses
 * (\App\Actions\Collections\Payments\ApprovePayment).
 *
 * Opened client-side by Bootstrap (`#requestPaymentsModal`) with the request's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. Only a
 * request that has been billed has anything to track, so one without an invoice
 * closes rather than opening onto an empty dialog.
 */
class RequestPaymentsModal extends AdminModalComponent
{
    const MODAL_ID = 'requestPaymentsModal';

    /** @var int|null ID of the request whose invoice is being tracked */
    public $viewingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestPaymentsModal'  => 'load',
        'closeRequestPaymentsModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var PaymentDetailQuery */
    protected $query;

    public function boot(RequestDetailQuery $detail, PaymentDetailQuery $query)
    {
        $this->detail = $detail;
        $this->query = $query;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('view', Request::class);

        $request = $this->detail->find($id);

        // Nothing to track until the office has raised the request's invoice.
        if (! $request->has_invoice) {
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->viewingRequestId = $request->id;
        $this->request = $request;

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->viewingRequestId = null;
        $this->request = null;

        parent::resetModal();
    }

    // =====================================================================
    // Approve
    // =====================================================================

    /**
     * Record a payment the homeowner filed against this request's invoice.
     *
     * The move is the ledger's own — only a receipt still waiting is approved,
     * and only one that belongs to this request's bill, so a stale page cannot
     * record money twice or against the wrong invoice.
     */
    public function approve(int $paymentId): void
    {
        if (! $this->loaded || ! $this->request || ! $this->request->bill) {
            return;
        }

        $this->authorize('approve', Payment::class);

        $payment = $this->query->find($paymentId);

        if (! $payment->is_pending || (int) $payment->bill_id !== (int) $this->request->bill->id) {
            return;
        }

        app(ApprovePayment::class)->handle($payment, auth('web')->user());

        $this->emit('notify', 'success', 'Payment approved — recorded against the invoice.');
        $this->emit('request-saved');

        // Re-read the record so the dialog shows the invoice and its receipts
        // as they now stand.
        $this->request = $this->detail->find($this->request->id);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-payments-modal', [
            'request'  => $this->request,
            'payments' => $this->request && $this->request->bill
                ? $this->query->forBill($this->request->bill)
                : collect(),
        ]);
    }
}
