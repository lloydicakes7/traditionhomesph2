<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;

/**
 * View Advance Deposit — one account's credit, read-only: what was received,
 * what is left, and every bill it has settled.
 *
 * Opened client-side by Bootstrap (`#advanceDepositViewModal`) with the
 * deposit's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 */
class AdvanceDepositViewModal extends AdminModalComponent
{
    const MODAL_ID = 'advanceDepositViewModal';

    /** @var int|null Deposit being shown */
    public $depositId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdvanceDepositViewModal'  => 'load',
        'closeAdvanceDepositViewModal' => 'closeModal',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    public function boot(AdvanceDepositListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Load — the shell is already on screen when this runs
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', AdvanceDeposit::class);

        $this->depositId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->depositId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-view-modal', [
            'deposit' => $this->depositId ? $this->query->find($this->depositId) : null,
        ]);
    }
}
