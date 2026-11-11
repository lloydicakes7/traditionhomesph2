<?php

namespace App\Http\Livewire\Admin\Collections\Payments\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;
use App\Queries\Collections\Payments\PaymentDetailQuery;

/**
 * View Payment — one receipt, read-only: what it settled, how it arrived, who
 * received it and who it came from.
 *
 * Opened client-side by Bootstrap (`#paymentViewModal`) with the payment's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js.
 */
class PaymentViewModal extends AdminModalComponent
{
    /** @var int|null Payment being shown */
    public $paymentId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPaymentViewModal'  => 'load',
        'closePaymentViewModal' => 'closeModal',
    ];

    /** @var PaymentDetailQuery */
    protected $query;

    public function boot(PaymentDetailQuery $query)
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

        $this->authorize('viewAny', Payment::class);

        $this->paymentId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->paymentId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.payments.modals.payment-view-modal', [
            'payment' => $this->paymentId ? $this->query->find($this->paymentId) : null,
        ]);
    }
}
