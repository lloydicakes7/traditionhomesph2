<?php

namespace App\Http\Livewire\Admin\Collections\Payments\Modals;

use App\Actions\Collections\Payments\DeletePayments;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;

/**
 * Delete Selected Payments — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class PaymentMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'paymentMassDeleteModal';

    /** @var array Payment ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPaymentMassDeleteModal'  => 'load',
        'closePaymentMassDeleteModal' => 'closeModal',
    ];

    /** @var DeletePayments */
    protected $deletePayments;

    public function boot(DeletePayments $deletePayments)
    {
        $this->deletePayments = $deletePayments;
    }

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
        return Payment::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'payments';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deletePayments->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'payment-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.payments.modals.payment-mass-delete-modal');
    }
}
