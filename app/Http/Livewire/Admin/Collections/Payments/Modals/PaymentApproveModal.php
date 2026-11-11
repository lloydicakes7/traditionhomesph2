<?php

namespace App\Http\Livewire\Admin\Collections\Payments\Modals;

use App\Actions\Collections\Payments\ApprovePayment;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;
use App\Queries\Collections\Payments\PaymentDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * Approve Payment — the confirmation a portal submission waits for.
 *
 * A payment filed from the homeowner portal is written as pending: the money
 * is claimed and the proof is on file, but the bill still stands for the full
 * amount until the office records it (see \App\Enums\PaymentStatus). This
 * dialog is that act — it records the receipt
 * and lets the bill settle from it (\App\Actions\Collections\Payments\ApprovePayment).
 *
 * Opened client-side by Bootstrap (`#paymentApproveModal`) with the payment's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. As it
 * loads it re-checks the receipt: one the office already recorded (or one
 * removed since the page was drawn) has nothing to approve, so the dialog says
 * so and closes rather than offering a move that no longer stands.
 */
class PaymentApproveModal extends AdminModalComponent
{
    const MODAL_ID = 'paymentApproveModal';

    /** @var int|null Payment the approval applies to */
    public $approvingPaymentId = null;

    /** @var string Amount and bill the approval names */
    public $approvingPaymentLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPaymentApproveModal'  => 'load',
        'closePaymentApproveModal' => 'closeModal',
    ];

    /** @var PaymentDetailQuery */
    protected $query;

    /** @var ApprovePayment */
    protected $approvePayment;

    public function boot(PaymentDetailQuery $query, ApprovePayment $approvePayment)
    {
        $this->query = $query;
        $this->approvePayment = $approvePayment;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('approve', Payment::class);

        $payment = $this->query->find($id);

        if (! $payment->is_pending) {
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->approvingPaymentId = $payment->id;
        $this->approvingPaymentLabel = $payment->amount_display . ' for ' . $payment->bill_reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->approvingPaymentId = null;
        $this->approvingPaymentLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Approve
    // =====================================================================

    public function approve(): void
    {
        if (! $this->loaded || ! $this->approvingPaymentId) {
            return;
        }

        $this->authorize('approve', Payment::class);

        $payment = $this->query->find($this->approvingPaymentId);

        // A stale page cannot record a receipt twice.
        if (! $payment->is_pending) {
            $this->resetModal();
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        try {
            $this->approvePayment->handle($payment, auth('web')->user());
        } catch (ValidationException $e) {
            $this->addError('approval', array_values($e->errors())[0][0] ?? 'That payment could not be approved.');

            return;
        }

        $this->emit('notify', 'success', 'Payment approved — recorded against the bill.');
        $this->emit('payment-approved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.payments.modals.payment-approve-modal', [
            'payment' => $this->approvingPaymentId
                ? $this->query->find($this->approvingPaymentId)
                : null,
        ]);
    }
}
