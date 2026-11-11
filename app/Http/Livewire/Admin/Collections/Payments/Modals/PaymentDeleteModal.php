<?php

namespace App\Http\Livewire\Admin\Collections\Payments\Modals;

use App\Actions\Collections\Payments\DeletePayments;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;
use App\Queries\Collections\Payments\PaymentDetailQuery;

/**
 * Delete Payment — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#paymentDeleteModal`) with the payment's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Removing a
 * receipt reopens the bill it settled and returns any advance-deposit credit it
 * drew back to that deposit, which is why the dialog says so before it runs.
 */
class PaymentDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'paymentDeleteModal';

    /** @var int|null Payment the confirmation applies to */
    public $deletingPaymentId = null;

    /** @var string Amount and bill the confirmation names */
    public $deletingPaymentLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPaymentDeleteModal'  => 'load',
        'closePaymentDeleteModal' => 'closeModal',
    ];

    /** @var PaymentDetailQuery */
    protected $query;

    /** @var DeletePayments */
    protected $deletePayments;

    public function boot(PaymentDetailQuery $query, DeletePayments $deletePayments)
    {
        $this->query = $query;
        $this->deletePayments = $deletePayments;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Payment::class);

        $payment = $this->query->find($id);

        $this->deletingPaymentId = $payment->id;
        $this->deletingPaymentLabel = $payment->amount_display . ' for ' . $payment->bill_reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingPaymentId = null;
        $this->deletingPaymentLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingPaymentId) {
            return;
        }

        $this->authorize('delete', Payment::class);

        $this->deletePayments->handle([$this->deletingPaymentId], auth('web')->user());

        $this->emit('notify', 'success', 'Payment deleted. The bill it settled has been reopened.');
        $this->emit('payment-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.payments.modals.payment-delete-modal');
    }
}
