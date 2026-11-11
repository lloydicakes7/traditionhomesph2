<?php

namespace App\Http\Livewire\Concerns;

use App\Actions\Homeowners\Households\UpdateHouseholdMember;
use App\Actions\Homeowners\Residents\FirstOrCreateResident;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Models\Household\HouseholdMember;
use App\Models\Resident;
use App\Queries\Homeowners\Residents\ResidentFormQuery;
use App\Utility\StorageHelper;
use Illuminate\Support\Arr;

trait ManagesHouseholdMembers
{
    // ===== State =====

    public $members = [];

    public $memberType = 'existing';

    public $memberPhoto;

    public $memberForm = [];

    /** @var int|null Staged member whose resident details the left panel is editing */
    public $editingMemberIndex = null;

    /** @var string Name shown above the edit form */
    public $editingResidentName = '';

    // ===== Rules =====

    protected function rulesForMember(): array
    {
        $relationship = [
            'memberForm.relationship' => ['required', 'string', 'max:100'],
            'memberForm.is_head'      => ['boolean'],
        ];

        if ($this->memberType === 'existing') {
            return ['memberForm.resident_id' => ['required', 'exists:residents,id']] + $relationship;
        }

        return ResidentRequest::rulesFor('memberForm') + $relationship + [
            'memberPhoto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    // ===== Actions =====

    public function openAddMemberForm(): void
    {
        $this->memberType = 'existing';
        $this->editingMemberIndex = null;
        $this->editingResidentName = '';
        $this->resetMemberForm();
        $this->resetValidation();
    }

    /**
     * Load a staged member's resident details into the left panel, so the office
     * edits the person in place instead of opening a second dialog.
     */
    public function startEditMember($index): void
    {
        $index = (int) $index;
        $member = $this->members[$index] ?? null;

        if (! $member) {
            return;
        }

        $resident = Resident::find((int) $member['resident_id']);

        if (! $resident) {
            return;
        }

        $this->memberType = 'edit';
        $this->editingMemberIndex = $index;
        $this->editingResidentName = $resident->full_name;
        $this->memberPhoto = null;
        $this->memberForm = array_merge(
            app(ResidentFormQuery::class)->form($resident),
            [
                'relationship' => $member['relationship'] ?? '',
                'is_head'      => (bool) ($member['is_head'] ?? false),
            ]
        );
        $this->resetValidation();
    }

    /**
     * Write the edited resident and relationship, then refresh that row of the
     * staged list with what was saved.
     */
    public function saveMemberEdit(): void
    {
        if ($this->editingMemberIndex === null) {
            return;
        }

        $this->validate($this->rulesForMember());

        $index = $this->editingMemberIndex;
        $residentId = (int) $this->members[$index]['resident_id'];
        $householdId = $this->membersHouseholdId();

        if (! $householdId) {
            return;
        }

        app(UpdateHouseholdMember::class)->handle(
            $residentId,
            $householdId,
            $this->memberForm,
            $this->memberPhoto,
            auth('web')->user()
        );

        $resident = Resident::find($residentId);

        $this->members[$index]['resident_name'] = $resident?->full_name ?? '—';
        $this->members[$index]['relationship'] = $this->memberForm['relationship'];
        $this->members[$index]['relationship_label'] = HouseholdMember::labelFor($this->memberForm['relationship']);
        $this->members[$index]['is_head'] = (bool) ($this->memberForm['is_head'] ?? false);

        if ($this->members[$index]['is_head']) {
            $this->markHead($index);
        }

        $this->openAddMemberForm();
    }

    public function addMember(): void
    {
        $this->validate($this->rulesForMember());

        $staged = $this->memberType === 'existing'
            ? $this->stageExistingResident()
            : $this->stageNewResident();

        if (! $staged) {
            return;
        }

        $this->keepSingleHead();
        $this->resetMemberForm();
    }

    /**
     * Flag one staged member as the family head, clearing the mark elsewhere.
     */
    public function markHead($index): void
    {
        foreach ($this->members as $position => $member) {
            $this->members[$position]['is_head'] = $position === (int) $index;
        }
    }

    public function removeMember($index): void
    {
        unset($this->members[$index]);
        $this->members = array_values($this->members);
    }

    // ===== Helpers =====

    private function stageExistingResident(): bool
    {
        $resident = Resident::find($this->memberForm['resident_id']);

        if ($error = $this->memberConflictError($resident?->id, 'memberForm.resident_id')) {
            $this->addError('memberForm.resident_id', $error);

            return false;
        }

        $this->members[] = $this->memberRow(
            (string) $this->memberForm['resident_id'],
            $resident?->full_name ?? '—'
        );

        return true;
    }

    private function stageNewResident(): bool
    {
        $resident = app(FirstOrCreateResident::class)->handle(
            Arr::except($this->memberForm, ['resident_id', 'relationship', 'is_head'])
        );

        if ($error = $this->memberConflictError($resident->id, 'memberForm.surname')) {
            $this->addError('memberForm.surname', $error);

            return false;
        }

        if ($this->memberPhoto && $resident->wasRecentlyCreated) {
            $resident->update([
                'profile_photo_path' => StorageHelper::storeResidentPhoto($this->memberPhoto),
            ]);
        }

        $this->members[] = $this->memberRow((string) $resident->id, $resident->full_name);

        return true;
    }

    private function memberRow(string $residentId, string $name): array
    {
        return [
            'id'                 => null,
            'resident_id'        => $residentId,
            'resident_name'      => $name,
            'relationship'       => $this->memberForm['relationship'],
            'relationship_label' => HouseholdMember::labelFor($this->memberForm['relationship']),
            'is_head'            => (bool) ($this->memberForm['is_head'] ?? false),
        ];
    }

    protected function memberConflictError(?int $residentId, string $errorKey): ?string
    {
        foreach ($this->members as $existingMember) {
            if ((string) $existingMember['resident_id'] === (string) $residentId) {
                return 'This resident is already a member of this household.';
            }
        }

        if ($residentId && HouseholdMember::residentIsPlaced($residentId)) {
            return 'This resident already belongs to a household.';
        }

        return null;
    }

    private function keepSingleHead(): void
    {
        $head = null;

        foreach ($this->members as $index => $member) {
            if (! empty($member['is_head'])) {
                $head = $index;
            }
        }

        foreach ($this->members as $index => $member) {
            $this->members[$index]['is_head'] = $index === $head;
        }
    }

    protected function resetMemberForm(): void
    {
        $this->memberPhoto = null;
        $this->memberForm = $this->blankMemberForm();
    }

    protected function resetMemberEdit(): void
    {
        $this->editingMemberIndex = null;
        $this->editingResidentName = '';
    }

    protected function resetMemberState(): void
    {
        $this->members = [];
        $this->memberType = 'existing';
        $this->resetMemberEdit();
        $this->resetMemberForm();
    }

    /**
     * A blank member form: who the member is, plus the resident's RBI profile.
     *
     * @return array<string, mixed>
     */
    protected function blankMemberForm(): array
    {
        return [
            'resident_id'  => '',
            'relationship' => '',
            'is_head'      => false,
        ] + app(ResidentFormQuery::class)->profileBlank();
    }

    // ===== Abstracts =====

    abstract protected function membersHeadResidentId(): ?int;

    abstract protected function membersHouseholdId(): ?int;
}
