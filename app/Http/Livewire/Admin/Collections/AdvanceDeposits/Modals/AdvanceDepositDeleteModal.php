<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Actions\Collections\AdvanceDeposits\DeleteAdvanceDeposits;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;

/**
 * Delete Advance Deposit — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#advanceDepositDeleteModal`) with the
 * deposit's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * A deposit that has settled a bill is refused here and points the officer at
 * Refund instead — the ledger keeps what it paid.
 */
class AdvanceDepositDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'advanceDepositDeleteModal';

    /** @var int|null Deposit the confirmation applies to */
    public $deletingDepositId = null;

    /** @var string Label the confirmation names */
    public $deletingDepositLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdvanceDepositDeleteModal'  => 'load',
        'closeAdvanceDepositDeleteModal' => 'closeModal',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    /** @var DeleteAdvanceDeposits */
    protected $deleteDeposits;

    public function boot(AdvanceDepositListQuery $query, DeleteAdvanceDeposits $deleteDeposits)
    {
        $this->query = $query;
        $this->deleteDeposits = $deleteDeposits;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', AdvanceDeposit::class);

        $deposit = $this->query->find($id);

        $this->deletingDepositId = $deposit->id;
        $this->deletingDepositLabel = $deposit->amount_display . ' held for ' . $deposit->user->email;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingDepositId = null;
        $this->deletingDepositLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingDepositId) {
            return;
        }

        $this->authorize('delete', AdvanceDeposit::class);

        $deleted = $this->deleteDeposits->handle([$this->deletingDepositId], auth('web')->user());

        if ($deleted) {
            $this->emit('notify', 'success', 'Advance deposit deleted.');
        } else {
            $this->emit('notify', 'warning', 'That deposit has settled a bill and cannot be deleted. Refund it instead.');
        }

        $this->emit('advance-deposit-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-delete-modal');
    }
}
