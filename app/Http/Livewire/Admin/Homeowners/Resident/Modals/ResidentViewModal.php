<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Resident;
use App\Queries\Homeowners\Residents\ResidentListQuery;

/**
 * "Resident Details" modal — read-only personal + account summary.
 *
 * Opened by native Bootstrap JS from the table's view button (carries
 * `data-id`); `modal-loader.js` emits `loadResidentViewModal`, which loads the
 * record through ResidentListQuery::find(). Emits nothing.
 */
class ResidentViewModal extends AdminModalComponent
{
    const MODAL_ID = 'residentViewModal';

    /** @var \App\Models\Resident|null */
    public $viewResident = null;

    protected $listeners = [
        'loadResidentViewModal'  => 'load',
        'closeResidentViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var ResidentListQuery */
    protected $query;

    public function boot(ResidentListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', Resident::class);

        $this->viewResident = $this->query->find((int) $id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewResident = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.resident.modals.resident-view-modal');
    }
}
