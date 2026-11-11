<?php

namespace App\Http\Livewire\Admin\Collections\Soa\Modals;

use App\Actions\Collections\Soa\DeleteStatementsOfAccount;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\SoaEmail;

/**
 * Delete Selected Statements — the confirmation the list's mass-delete bar
 * opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class SoaMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'soaMassDeleteModal';

    /** @var array Statement ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadSoaMassDeleteModal'  => 'load',
        'closeSoaMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteStatementsOfAccount */
    protected $deleteStatements;

    public function boot(DeleteStatementsOfAccount $deleteStatements)
    {
        $this->deleteStatements = $deleteStatements;
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
        return SoaEmail::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'statements';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteStatements->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'soa-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.soa.modals.soa-mass-delete-modal');
    }
}
