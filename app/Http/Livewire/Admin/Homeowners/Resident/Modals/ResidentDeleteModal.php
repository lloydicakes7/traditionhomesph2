<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident\Modals;

use App\Actions\Homeowners\Residents\DeleteResidents;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Resident;
use App\Queries\Homeowners\Residents\ResidentListQuery;

class ResidentDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'residentDeleteModal';

    public $deletingResidentId = null;

    public $deletingResidentLabel = null;

    protected $listeners = [
        'loadResidentDeleteModal'  => 'load',
        'closeResidentDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var ResidentListQuery */
    protected $query;

    /** @var DeleteResidents */
    protected $deleteResidents;

    public function boot(ResidentListQuery $query, DeleteResidents $deleteResidents)
    {
        $this->query = $query;
        $this->deleteResidents = $deleteResidents;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Resident::class);

        $resident = $this->query->find((int) $id);

        $this->deletingResidentId = $resident->id;
        $this->deletingResidentLabel = $resident->full_name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingResidentId = null;
        $this->deletingResidentLabel = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingResidentId) {
            return;
        }

        $this->authorize('delete', Resident::class);

        $this->deleteResidents->handle($this->deletingResidentId, auth('web')->user());

        $this->emit('notify', 'success', 'Resident deleted successfully.');
        $this->emit('resident-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.resident.modals.resident-delete-modal');
    }
}
