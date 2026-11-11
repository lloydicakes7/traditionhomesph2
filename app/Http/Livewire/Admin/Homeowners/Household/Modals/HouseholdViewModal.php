<?php

namespace App\Http\Livewire\Admin\Homeowners\Household\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Household\Household;
use App\Queries\Homeowners\Households\HouseholdListQuery;

/**
 * "Household Details" modal — read-only summary with members table.
 *
 * Opened by native Bootstrap JS from the table's view button (carries
 * `data-id`); `modal-loader.js` emits `loadHouseholdViewModal`, which loads the
 * record through HouseholdListQuery::view(). Emits nothing.
 */
class HouseholdViewModal extends AdminModalComponent
{
    const MODAL_ID = 'householdViewModal';

    /** @var array|null Household data for the view modal */
    public $viewData = null;

    protected $listeners = [
        'loadHouseholdViewModal'  => 'load',
        'closeHouseholdViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HouseholdListQuery */
    protected $query;

    public function boot(HouseholdListQuery $query)
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

        $this->authorize('viewAny', Household::class);

        $this->viewData = $this->query->view((int) $id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.household.modals.household-view-modal');
    }
}
