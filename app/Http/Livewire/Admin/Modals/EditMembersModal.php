<?php

namespace App\Http\Livewire\Admin\Modals;

use App\Actions\Homeowners\Households\SaveHouseholdMembers;
use App\Enums\CivilStatus;
use App\Http\Livewire\Concerns\ManagesHouseholdMembers;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Household\Household;
use App\Queries\Homeowners\Households\HouseholdMemberQuery;
use App\Queries\Homeowners\Residents\ResidentFormQuery;

/**
 * The shared "Edit Household Members" dialog.
 *
 * Opened by native Bootstrap JS from an "Edit Members" item (`data-toggle` +
 * `data-target` + `data-id`, the household id); public/js/modal-loader.js
 * relays that as `loadEditMembersModal` with the id, and the dialog fetches the
 * members itself. A member's pencil loads their resident details into the left
 * panel, so nothing opens a second dialog. Saving emits `edit-members-saved`.
 */
class EditMembersModal extends AdminModalComponent
{
    use ManagesHouseholdMembers;

    public $householdId = null;

    protected $listeners = [
        'loadEditMembersModal'  => 'load',
        'openEditMembersModal'  => 'refreshModal',
        'closeEditMembersModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HouseholdMemberQuery */
    protected $query;

    /** @var ResidentFormQuery */
    protected $residentForm;

    /** @var SaveHouseholdMembers */
    protected $saveMembers;

    public function boot(
        HouseholdMemberQuery $query,
        ResidentFormQuery $residentForm,
        SaveHouseholdMembers $saveMembers
    ) {
        $this->query = $query;
        $this->residentForm = $residentForm;
        $this->saveMembers = $saveMembers;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load($householdId): void
    {
        $householdId = (int) $householdId;

        if (! $householdId) {
            return;
        }

        $this->authorize('update', Household::class);

        $this->householdId = $householdId;
        $this->resetMemberState();
        $this->resetValidation();

        $this->members = $this->query->rows($this->householdId);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->householdId = null;

        $this->resetMemberState();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded || ! $this->householdId) {
            return;
        }

        $this->authorize('update', Household::class);

        $this->saveMembers->handle($this->householdId, $this->members, auth('web')->user());

        $this->emit('notify', 'success', 'Household members updated successfully.');

        $this->emit('edit-members-saved', $this->householdId);

        $this->dispatchBrowserEvent('modal-close', ['id' => 'editMembersModal']);
    }

    protected function membersHeadResidentId(): ?int
    {
        if (! $this->householdId) {
            return null;
        }

        return Household::with('user')->find($this->householdId)?->user?->resident_id;
    }

    protected function membersHouseholdId(): ?int
    {
        return $this->householdId;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.modals.edit-members-modal', [
            'selectableResidents' => $this->loaded
                ? $this->query->selectable($this->membersHeadResidentId())
                : collect(),
            'showSpouse' => CivilStatus::isMarried((string) ($this->memberForm['civil_status'] ?? '')),
        ] + ($this->loaded ? $this->residentForm->options() : $this->residentForm->blank()));
    }
}
