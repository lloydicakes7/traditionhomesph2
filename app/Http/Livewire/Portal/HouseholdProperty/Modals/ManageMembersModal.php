<?php

namespace App\Http\Livewire\Portal\HouseholdProperty\Modals;

use App\Actions\Homeowners\Households\AddHouseholdMember;
use App\Actions\Homeowners\Households\RemoveHouseholdMember;
use App\Actions\Homeowners\Households\UpdateHouseholdMember;
use App\Actions\Homeowners\Residents\FirstOrCreateResident;
use App\Http\Livewire\PortalModalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Queries\Homeowners\Households\HouseholdMemberQuery;
use App\Queries\Homeowners\Residents\ResidentFormQuery;

/**
 * "Manage Members" modal — the homeowner keeping their own household's member
 * list.
 *
 * Opened by native Bootstrap JS: the Household / Property page's button carries
 * `data-toggle` and `data-id`, and `public/js/modal-loader.js` turns that into
 * `loadManageMembersModal`. The shell is on screen at the click; the members
 * are fetched on that load. Emits `household-members-updated` after every
 * change, which refreshes the page's list.
 *
 * Three things can happen here: a member is added, a member's details are
 * edited, or a member is removed — and each one is written as it is made rather
 * than held in a list to save. A household has a handful of members, so there
 * is nothing to stage, and the record a homeowner changes is theirs to see
 * immediately. Removal is the one destructive move, so it is confirmed through
 * the association's shared delete dialog before anything is deleted.
 *
 * A member is entered here as a person — the portal never offers a picker of
 * residents already on file, because another household's residents are not the
 * homeowner's to browse or claim. Nothing is re-invented: the person is matched
 * or created by the same FirstOrCreateResident action the office uses, the
 * "one household per resident" rule is HouseholdMember::residentIsPlaced(), and
 * the profile rules are the office's own ResidentRequest, so a member recorded
 * from the portal is the same record the office would have written. Each change
 * authorizes ownership first, through the portal side of the HouseholdPolicy
 * (`updateOwn`).
 */
class ManageMembersModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'manageMembersModal';

    /** @var int|null Household whose members are being managed */
    public $householdId = null;

    /** @var string Which of the modal's two jobs the form is doing: new, edit */
    public $mode = 'new';

    /** @var string Relationship of the member being added or edited */
    public $relationship = '';

    /** @var bool Whether that member is the family's head */
    public $isHead = false;

    /** @var int|null Resident whose details are being edited */
    public $editingResidentId = null;

    /** @var int|null Resident the removal confirmation was opened for */
    public $removingResidentId = null;

    /** @var array The person's profile, as the portal's own fields key it */
    public $form = [
        'surname'                => '',
        'first_name'             => '',
        'middle_name'            => '',
        'suffix'                 => '',
        'birth_date'             => '',
        'birth_place'            => '',
        'sex'                    => '',
        'civil_status'           => '',
        'citizenship'            => '',
        'religion'               => '',
        'occupation'             => '',
        'mobile_no'              => '',
        'educational_attainment' => '',
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadManageMembersModal'  => 'load',
        'closeManageMembersModal' => 'closeModal',
    ];

    /** @var HouseholdMemberQuery */
    protected $query;

    /** @var ResidentFormQuery */
    protected $formQuery;

    /** @var FirstOrCreateResident */
    protected $firstOrCreate;

    /** @var AddHouseholdMember */
    protected $addMemberAction;

    /** @var UpdateHouseholdMember */
    protected $updateMemberAction;

    /** @var RemoveHouseholdMember */
    protected $removeMemberAction;

    public function boot(
        HouseholdMemberQuery $query,
        ResidentFormQuery $formQuery,
        FirstOrCreateResident $firstOrCreate,
        AddHouseholdMember $addMemberAction,
        UpdateHouseholdMember $updateMemberAction,
        RemoveHouseholdMember $removeMemberAction
    ) {
        $this->query = $query;
        $this->formQuery = $formQuery;
        $this->firstOrCreate = $firstOrCreate;
        $this->addMemberAction = $addMemberAction;
        $this->updateMemberAction = $updateMemberAction;
        $this->removeMemberAction = $removeMemberAction;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    /**
     * A person being entered or edited is held to the profile rules the
     * office's resident form uses, so both panels ask for the same record the
     * same way; the portal adds only the relationship the household holds them
     * under.
     *
     * @return array
     */
    protected function rules(): array
    {
        return (new ResidentRequest)->rules() + [
            'form.birth_place'     => ['nullable', 'string', 'max:255'],
            'relationship'         => ['required', 'string', 'max:100'],
            'isHead'               => ['boolean'],
        ];
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    /**
     * Load the members of a household the signed-in account owns.
     *
     * @param  mixed  $householdId
     * @return void
     */
    public function load($householdId): void
    {
        $this->householdId = (int) $householdId;

        $household = $this->household();

        if ($household) {
            $this->authorize('updateOwn', $household);
        }

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the household and the form when the shell closes, so the next open
     * never inherits them.
     */
    protected function resetModal(): void
    {
        $this->householdId = null;
        $this->resetForm();

        parent::resetModal();
    }

    /**
     * Leave the edit form and go back to adding a person.
     *
     * @return void
     */
    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->resetValidation();
    }

    // =====================================================================
    // Add
    // =====================================================================

    /**
     * Add a person to the account's household — matched to a resident already
     * on file, or created, exactly as the office's own member form does.
     *
     * @return void
     */
    public function addMember(): void
    {
        $household = $this->authorizedHousehold();

        $this->validate($this->rules());

        $resident = $this->firstOrCreate->handle($this->form);

        if ($error = $this->conflictError($resident->id)) {
            $this->addError('form.surname', $error);

            return;
        }

        $this->attachMember($household, $resident->id);

        $this->resetForm();

        $this->emit('notify', 'success', 'Member added to your household.');
        $this->emit('household-members-updated');
    }

    // =====================================================================
    // Edit
    // =====================================================================

    /**
     * Load one of the household's members into the form for editing.
     *
     * @param  mixed  $residentId
     * @return void
     */
    public function editMember($residentId): void
    {
        $household = $this->authorizedHousehold();

        $member = $this->memberRecord($household, (int) $residentId);

        if (! $member || ! $member->resident) {
            return;
        }

        $resident = $member->resident;

        $this->mode = 'edit';
        $this->editingResidentId = $resident->id;
        $this->relationship = $member->relationship ?? '';
        $this->isHead = (bool) $member->is_head;
        $this->form = [
            'surname'                => $resident->surname ?? '',
            'first_name'             => $resident->first_name ?? '',
            'middle_name'            => $resident->middle_name ?? '',
            'suffix'                 => $resident->suffix ?? '',
            'birth_date'             => $resident->birth_date ? $resident->birth_date->format('Y-m-d') : '',
            'birth_place'            => $resident->birth_place ?? '',
            'sex'                    => $resident->sex ?? '',
            'civil_status'           => $resident->civil_status ?? '',
            'citizenship'            => $resident->citizenship ?? '',
            'religion'               => $resident->religion ?? '',
            'occupation'             => $resident->occupation ?? '',
            'mobile_no'              => $resident->mobile_no ?? '',
            'educational_attainment' => $resident->educational_attainment ?? '',
        ];

        $this->resetValidation();
    }

    /**
     * Save the member's details and the relationship the household holds them
     * under.
     *
     * @return void
     */
    public function updateMember(): void
    {
        $household = $this->authorizedHousehold();

        $member = $this->memberRecord($household, (int) $this->editingResidentId);

        if (! $member || ! $member->resident) {
            $this->resetForm();

            return;
        }

        $this->validate($this->rules());

        $this->updateMemberAction->handle(
            $member->resident_id,
            $household->id,
            $this->form + [
                'relationship' => $this->relationship,
                'is_head'      => $this->isHead,
            ],
            null
        );

        $this->resetForm();

        $this->emit('notify', 'success', 'Member updated.');
        $this->emit('household-members-updated');
    }

    // =====================================================================
    // Remove
    // =====================================================================

    /**
     * Ask the homeowner to confirm taking a member off the household — nothing
     * is deleted until they say so.
     *
     * @param  mixed  $residentId
     * @return void
     */
    public function confirmRemove($residentId): void
    {
        $household = $this->authorizedHousehold();

        $member = $this->memberRecord($household, (int) $residentId);

        if (! $member) {
            return;
        }

        $this->removingResidentId = (int) $residentId;

        $this->dispatchBrowserEvent('modal-open', ['id' => 'removeHouseholdMemberModal']);
    }

    /**
     * Take the member the confirmation was opened for off the household.
     *
     * The person stays on file: the association keeps the resident it profiled,
     * and removing them from this household is not the same as erasing them.
     *
     * @return void
     */
    public function removeMember(): void
    {
        $household = $this->authorizedHousehold();

        if (! $this->removingResidentId) {
            return;
        }

        $member = $this->memberRecord($household, $this->removingResidentId);

        if ($member) {
            $this->removeMemberAction->handle($household->id, $this->removingResidentId);
        }

        if ((int) $this->editingResidentId === $this->removingResidentId) {
            $this->resetForm();
        }

        $this->removingResidentId = null;

        $this->dispatchBrowserEvent('modal-close', ['id' => 'removeHouseholdMemberModal']);

        $this->emit('notify', 'info', 'Member removed from your household.');
        $this->emit('household-members-updated');
    }

    // =====================================================================
    // Tools
    // =====================================================================

    /**
     * The household being managed, with a fresh check that it is the signed-in
     * account's.
     *
     * @return \App\Models\Household\Household
     */
    private function authorizedHousehold(): Household
    {
        $household = $this->household();

        if (! $household) {
            abort(404);
        }

        $this->authorize('updateOwn', $household);

        return $household;
    }

    /**
     * The household the modal was opened for, resolved through the signed-in
     * account rather than by id alone: the query can only ever answer with the
     * homeowner's own household, so no render can be made to show another
     * household's members.
     *
     * @return \App\Models\Household\Household|null
     */
    private function household(): ?Household
    {
        if (! $this->householdId) {
            return null;
        }

        return $this->account()->household()->whereKey($this->householdId)->first();
    }

    /**
     * One of a household's member records, when it holds the resident.
     *
     * @param  \App\Models\Household\Household  $household
     * @param  int  $residentId
     * @return \App\Models\Household\HouseholdMember|null
     */
    private function memberRecord(Household $household, int $residentId): ?HouseholdMember
    {
        return HouseholdMember::with('resident')
            ->where('household_id', $household->id)
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * Put a resident on the household's member list under the relationship the
     * form holds.
     *
     * @param  \App\Models\Household\Household  $household
     * @param  int  $residentId
     * @return void
     */
    private function attachMember(Household $household, int $residentId): void
    {
        $this->addMemberAction->handle(
            $household->id,
            $residentId,
            $this->relationship,
            $this->isHead
        );
    }

    /**
     * Make one of the household's members its head, without touching their
     * profile. The account holder may hand the head role to a family member.
     */
    public function markHead($residentId): void
    {
        $household = $this->authorizedHousehold();

        $member = $this->memberRecord($household, (int) $residentId);

        if (! $member) {
            return;
        }

        $household->designateHead($member->resident_id);

        $this->emit('notify', 'success', 'Head of the family updated.');
        $this->emit('household-members-updated');
    }

    /**
     * Why the given resident cannot join this household, or null when they can.
     * The head is the account holder; a resident lives in one household at a
     * time.
     *
     * @param  int  $residentId
     * @return string|null
     */
    private function conflictError(int $residentId): ?string
    {
        if ($residentId === (int) $this->account()->resident_id) {
            return 'That is the account holder and cannot be added as a member.';
        }

        if (HouseholdMember::residentIsPlaced($residentId)) {
            return 'That resident already belongs to a household.';
        }

        return null;
    }

    /**
     * The member the removal confirmation is showing, when one is open.
     *
     * @param  array  $members
     * @return array|null
     */
    private function removingMember(array $members): ?array
    {
        if (! $this->removingResidentId) {
            return null;
        }

        foreach ($members as $member) {
            if ((int) $member['resident_id'] === $this->removingResidentId) {
                return $member;
            }
        }

        return null;
    }

    /**
     * Clear the form back to adding a person.
     *
     * @return void
     */
    private function resetForm(): void
    {
        $this->mode = 'new';
        $this->relationship = '';
        $this->isHead = false;
        $this->editingResidentId = null;
        $this->removingResidentId = null;
        $this->form = [
            'surname'                => '',
            'first_name'             => '',
            'middle_name'            => '',
            'suffix'                 => '',
            'birth_date'             => '',
            'birth_place'            => '',
            'sex'                    => '',
            'civil_status'           => '',
            'citizenship'            => '',
            'religion'               => '',
            'occupation'             => '',
            'mobile_no'              => '',
            'educational_attainment' => '',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the member form, the household's current members, and the member
     * the removal confirmation is showing.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Not loaded, the modal shows only its skeleton and needs neither the
        // household's members nor the resident form's options. The view still
        // receives the empty shapes so it never reads an undefined variable.
        $household = $this->loaded ? $this->household() : null;

        $members = $household ? $this->query->rows($household->id) : [];

        return view('livewire.portal.household-property.modals.manage-members-modal', [
            'members'        => $members,
            'removingMember' => $this->loaded ? $this->removingMember($members) : null,
        ] + ($this->loaded ? $this->formQuery->options() : $this->formQuery->blank()));
    }
}
