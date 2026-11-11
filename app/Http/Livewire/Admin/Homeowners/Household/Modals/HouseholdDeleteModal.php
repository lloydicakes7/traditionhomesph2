<?php

namespace App\Http\Livewire\Admin\Homeowners\Household\Modals;

use App\Actions\Homeowners\Households\DeleteHouseholds;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Household\Household;
use App\Queries\Homeowners\Households\HouseholdListQuery;

class HouseholdDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'householdDeleteModal';

    public $deletingHouseholdId = null;

    public $deletingHouseholdLabel = null;

    protected $listeners = [
        'loadHouseholdDeleteModal'  => 'load',
        'closeHouseholdDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HouseholdListQuery */
    protected $query;

    /** @var DeleteHouseholds */
    protected $deleteHouseholds;

    public function boot(HouseholdListQuery $query, DeleteHouseholds $deleteHouseholds)
    {
        $this->query = $query;
        $this->deleteHouseholds = $deleteHouseholds;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Household::class);

        $household = $this->query->find((int) $id);

        $this->deletingHouseholdId = $household->id;
        $this->deletingHouseholdLabel = $household->display_name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingHouseholdId = null;
        $this->deletingHouseholdLabel = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingHouseholdId) {
            return;
        }

        $this->authorize('delete', Household::class);
        $this->deleteHouseholds->handle($this->deletingHouseholdId, auth('web')->user());

        $this->emit('notify', 'success', 'Household and associated data deleted successfully.');
        $this->emit('household-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.household.modals.household-delete-modal');
    }
}
