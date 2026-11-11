<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Actions\Collections\AdvanceDeposits\DeleteAdvanceDeposits;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;

/**
 * Delete Selected Advance Deposits — the confirmation the list's mass-delete
 * bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class AdvanceDepositMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'advanceDepositMassDeleteModal';

    /** @var array Deposit ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdvanceDepositMassDeleteModal'  => 'load',
        'closeAdvanceDepositMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteAdvanceDeposits */
    protected $deleteDeposits;

    public function boot(DeleteAdvanceDeposits $deleteDeposits)
    {
        $this->deleteDeposits = $deleteDeposits;
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
        return AdvanceDeposit::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'advance deposits';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteDeposits->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'advance-deposit-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-mass-delete-modal');
    }
}
