<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident\Modals;

use App\Actions\Homeowners\Residents\DeleteResidents;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Models\Resident;

/**
 * Delete Selected Residents — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class ResidentMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'residentMassDeleteModal';

    public $selectedIds = [];

    protected $listeners = [
        'loadResidentMassDeleteModal'  => 'load',
        'closeResidentMassDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DeleteResidents */
    protected $deleteResidents;

    public function boot(DeleteResidents $deleteResidents)
    {
        $this->deleteResidents = $deleteResidents;
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
        return Resident::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'residents';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteResidents->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'resident-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.resident.modals.resident-mass-delete-modal');
    }
}
