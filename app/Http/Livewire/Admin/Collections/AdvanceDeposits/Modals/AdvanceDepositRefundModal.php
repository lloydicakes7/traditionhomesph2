<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Actions\Collections\AdvanceDeposits\RefundAdvanceDeposit;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;

/**
 * Refund Advance Deposit — the confirmation a row's Refund action opens.
 *
 * Opened client-side by Bootstrap (`#advanceDepositRefundModal`) with the
 * deposit's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * What is left of the credit is returned to the homeowner and the deposit is
 * closed; unlike a delete, the record is kept.
 */
class AdvanceDepositRefundModal extends AdminModalComponent
{
    const MODAL_ID = 'advanceDepositRefundModal';

    /** @var int|null Deposit the confirmation applies to */
    public $refundingDepositId = null;

    /** @var string Label the confirmation names */
    public $refundingDepositLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdvanceDepositRefundModal'  => 'load',
        'closeAdvanceDepositRefundModal' => 'closeModal',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    /** @var RefundAdvanceDeposit */
    protected $refundDeposit;

    public function boot(AdvanceDepositListQuery $query, RefundAdvanceDeposit $refundDeposit)
    {
        $this->query = $query;
        $this->refundDeposit = $refundDeposit;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('refund', AdvanceDeposit::class);

        $deposit = $this->query->find($id);

        $this->refundingDepositId = $deposit->id;
        $this->refundingDepositLabel = $deposit->balance_display . ' held for ' . $deposit->property_label;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->refundingDepositId = null;
        $this->refundingDepositLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Refund
    // =====================================================================

    public function refund(): void
    {
        if (! $this->loaded || ! $this->refundingDepositId) {
            return;
        }

        $this->authorize('refund', AdvanceDeposit::class);

        $deposit = $this->refundDeposit->handle(
            $this->query->find($this->refundingDepositId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Advance deposit for ' . $deposit->property_label . ' refunded.');
        $this->emit('advance-deposit-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-refund-modal');
    }
}
