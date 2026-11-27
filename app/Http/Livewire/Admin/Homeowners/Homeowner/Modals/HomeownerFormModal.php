<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Actions\Homeowners\Households\AddHouseholdMember;
use App\Actions\Homeowners\Households\CreateHousehold;
use App\Actions\Homeowners\Households\UpdateHousehold;
use App\Actions\Homeowners\Properties\CreateProperty;
use App\Actions\Homeowners\Residents\CreateResident;
use App\Actions\Homeowners\Residents\UpdateResident;
use App\Actions\Homeowners\Users\CreateUserAccount;
use App\Actions\Homeowners\Users\UpdateUserAccount;
use App\Enums\CivilStatus;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\ValidatesSectoralTags;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Rules\HouseholdHeadAge;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Resident;
use App\Models\User;
use App\Queries\Homeowners\Households\HouseholdUnitQuery;
use App\Queries\Homeowners\Residents\ResidentFormQuery;
use App\Queries\Homeowners\Users\UserDetailQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class HomeownerFormModal extends AdminModalComponent
{
    use ValidatesSectoralTags, WithFileUploads;

    const MODAL_ID = 'homeownerFormModal';

    /** @var int|null Account being edited; null while creating */
    public $editingUserId = null;

    /** @var array The account and profile fields, in one form */
    public $form = [];

    /** @var array<string, mixed> The unit the household lives in */
    public $property = [
        'block' => '',
        'lot'   => '',
        'sqm'   => '',
        'type'  => '',
    ];

    /** @var array<string, mixed> The household's own facts */
    public $household = [
        'type'         => HouseholdType::OWNER,
        'prev_address' => '',
        'relationship' => '',
        'be_head'      => false,
    ];

    /** @var int|null Household on file for the resident being edited */
    public $householdId = null;

    /** @var array<string, mixed>|null What the office holds for the named unit */
    public $unit = null;

    /** @var mixed Uploaded profile photo (temporary) */
    public $profilePhoto;

    /** @var string|null Existing photo URL for preview */
    public $existingPhotoUrl = null;

    /** @var bool Whether to remove the existing profile photo on save */
    public $removeProfilePhoto = false;

    protected $listeners = [
        'openHomeownerFormModal'  => 'openCreate',
        'loadHomeownerFormModal'  => 'load',
        'closeHomeownerFormModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var UserDetailQuery */
    protected $users;

    /** @var ResidentFormQuery */
    protected $formQuery;

    /** @var HouseholdUnitQuery */
    protected $units;

    /** @var CreateResident */
    protected $createResident;

    /** @var UpdateResident */
    protected $updateResident;

    /** @var CreateUserAccount */
    protected $createAccount;

    /** @var UpdateUserAccount */
    protected $updateAccount;

    /** @var CreateHousehold */
    protected $createHousehold;

    /** @var UpdateHousehold */
    protected $updateHousehold;

    /** @var CreateProperty */
    protected $createProperty;

    /** @var AddHouseholdMember */
    protected $addMember;

    public function boot(
        UserDetailQuery $users,
        ResidentFormQuery $formQuery,
        HouseholdUnitQuery $units,
        CreateResident $createResident,
        UpdateResident $updateResident,
        CreateUserAccount $createAccount,
        UpdateUserAccount $updateAccount,
        CreateHousehold $createHousehold,
        UpdateHousehold $updateHousehold,
        CreateProperty $createProperty,
        AddHouseholdMember $addMember
    ) {
        $this->users = $users;
        $this->formQuery = $formQuery;
        $this->units = $units;
        $this->createResident = $createResident;
        $this->updateResident = $updateResident;
        $this->createAccount = $createAccount;
        $this->updateAccount = $updateAccount;
        $this->createHousehold = $createHousehold;
        $this->updateHousehold = $updateHousehold;
        $this->createProperty = $createProperty;
        $this->addMember = $addMember;
    }

    // ===== Rules =====

    protected function rules(): array
    {
        $rules = [
            'form.email'             => [
                'required', 'email', 'max:255',
                'unique:users,email,' . ($this->editingUserId ?? 'NULL') . ',id',
            ],
            'form.is_active'         => ['boolean'],
            'profilePhoto'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'property.block'         => [$this->editingUserId ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'property.lot'           => [$this->editingUserId ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'property.sqm'           => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'property.type'          => [
                Rule::requiredIf(function () {
                    return $this->householdIsNewUnit();
                }),
                'nullable', Rule::in(PropertyType::values()),
            ],
            'household.type'         => ['required', Rule::in(HouseholdType::values())],
            'household.prev_address' => ['nullable', 'string', 'max:500'],
            'household.relationship' => ['nullable', 'string', 'max:100'],
            'household.be_head'      => ['boolean'],
        ];

        $rules = array_merge($rules, ResidentRequest::rulesFor('form'), $this->sectoralTagRules('form'));

        if ($this->residentBecomesHead()) {
            $rules['form.birth_date'] = array_merge($rules['form.birth_date'], [new HouseholdHeadAge]);
        }

        return $rules;
    }

    private function residentBecomesHead(): bool
    {
        if ($this->editingUserId) {
            return $this->householdId !== null;
        }

        return (bool) ($this->household['be_head'] ?? false)
            || $this->unit === null
            || ! ($this->unit['is_occupied'] ?? false);
    }

    // ===== Lifecycle =====

    public function openCreate(): void
    {
        $this->authorize('create', User::class);

        $this->editingUserId = null;
        $this->fillFromUser(null);

        $this->markLoaded();
    }

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', User::class);
        $this->authorize('update', Resident::class);

        $user = $this->users->find((int) $id);

        if (! $user->resident) {
            return;
        }

        $this->editingUserId = $user->id;
        $this->fillFromUser($user);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->editingUserId = null;
        $this->form = [];
        $this->property = [
            'block' => '',
            'lot'   => '',
            'sqm'   => '',
            'type'  => '',
        ];
        $this->household = [
            'type'         => HouseholdType::OWNER,
            'prev_address' => '',
            'relationship' => '',
            'be_head'      => false,
        ];
        $this->householdId = null;
        $this->unit = null;
        $this->profilePhoto = null;
        $this->existingPhotoUrl = null;
        $this->removeProfilePhoto = false;

        parent::resetModal();
    }

    /** A changed block or lot is read back from the office's own records. */
    public function updated($name): void
    {
        if ($name === 'form.sectors') {
            $this->syncSectoralTags('form');

            return;
        }

        if ($name !== 'property.block' && $name !== 'property.lot') {
            return;
        }

        $this->household['be_head'] = false;
        $this->resetErrorBag();
        $this->refreshUnit();
    }

    private function refreshUnit(): void
    {
        $this->unit = $this->unitIsNamed()
            ? $this->units->atUnit((int) $this->property['block'], (int) $this->property['lot'])
            : null;
    }

    private function householdIsNewUnit(): bool
    {
        return $this->unitIsNamed() && $this->unit === null;
    }

    // ===== Actions =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorizeSave();
        $this->refreshUnit();
        $this->validate();

        if (! $this->unitIsResolvable()) {
            return;
        }

        $by = auth('web')->user();

        DB::transaction(function () use ($by) {
            $resident = $this->persistResident($by);

            $this->persistHousehold($resident, $by);
            $this->persistAccount($resident, $by);
        });

        $this->emit('notify', 'success', $this->editingUserId ? 'Homeowner updated successfully.' : 'Homeowner created successfully.');
        $this->emit('homeowner-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    public function removePhoto(): void
    {
        $this->authorizeSave();

        $this->removeProfilePhoto = true;
        $this->existingPhotoUrl = null;
        $this->profilePhoto = null;

        $this->emit('notify', 'info', $this->editingUserId ? 'Profile photo will be removed on save.' : 'Profile photo removed.');
    }

    // ===== Helpers =====

    private function authorizeSave(): void
    {
        if (! $this->editingUserId) {
            $this->authorize('create', User::class);

            return;
        }

        $this->authorize('update', User::class);
        $this->authorize('update', Resident::class);
    }

    private function fillFromUser(?User $user): void
    {
        $this->form = [
            'email'     => $user->email ?? '',
            'is_active' => $user->is_active ?? true,
        ] + ($user ? $this->formQuery->form($user->resident) : $this->formQuery->blankForm());

        $this->fillHousehold($user?->resident?->household);

        $this->profilePhoto = null;
        $this->existingPhotoUrl = $user?->resident?->photo_url;
        $this->removeProfilePhoto = false;

        $this->resetValidation();
    }

    private function fillHousehold(?Household $household): void
    {
        $property = $household?->property;

        $this->property = [
            'block' => $property ? (string) $property->block : '',
            'lot'   => $property ? (string) $property->lot : '',
            'sqm'   => '',
            'type'  => '',
        ];

        $this->household = [
            'type'         => $household?->type ?: HouseholdType::OWNER,
            'prev_address' => $household?->prev_address ?? '',
            'relationship' => '',
            'be_head'      => false,
        ];

        $this->householdId = $household ? (int) $household->id : null;
        $this->unit = $property
            ? $this->units->atUnit((int) $property->block, (int) $property->lot)
            : null;
    }

    private function unitIsNamed(): bool
    {
        return (int) ($this->property['block'] ?? 0) > 0 && (int) ($this->property['lot'] ?? 0) > 0;
    }

    private function unitIsOwnHousehold(): bool
    {
        return $this->householdId !== null
            && $this->unit !== null
            && (int) $this->unit['household_id'] === $this->householdId;
    }

    /**
     * A named unit is read back first, so the form asks only for what is still
     * missing: a new unit's floor area and type, and the household's own facts.
     * The household on file moves with this homeowner, so an occupied unit is
     * refused only while editing; a new homeowner joins it instead.
     */
    private function unitIsResolvable(): bool
    {
        if (! $this->unitIsNamed()) {
            return (bool) $this->editingUserId;
        }

        if (! $this->unit && $this->units->associationAddress() === null) {
            $this->addError('property.block', __('households.note.no_association_address'));

            return false;
        }

        if ($this->unit && $this->unit['is_occupied'] && ! $this->unitIsOwnHousehold() && $this->householdId !== null) {
            $this->addError('property.block', __('households.note.occupied_move'));

            return false;
        }

        return true;
    }

    private function unitNote(): string
    {
        if (! $this->unitIsNamed()) {
            return $this->editingUserId
                ? __('households.note.not_placed')
                : __('households.note.name_unit');
        }

        if ($this->unit === null) {
            return __('households.note.new_unit');
        }

        if ($this->unitIsOwnHousehold()) {
            return __('households.note.own_household');
        }

        if ($this->unit['is_occupied']) {
            return $this->householdId !== null
                ? __('households.note.occupied_move')
                : __('households.note.occupied');
        }

        return __('households.note.vacant');
    }

    private function unitCanJoin(): bool
    {
        return ! $this->unitIsOwnHousehold()
            && ! ($this->unit !== null && $this->unit['is_occupied'] && $this->householdId !== null);
    }

    private function unitCanChangeTenure(): bool
    {
        return $this->unit === null || ! $this->unit['is_occupied'] || $this->unitIsOwnHousehold();
    }

    private function persistResident(Admin $by): Resident
    {
        if (! $this->editingUserId) {
            return $this->createResident->handle($this->form, $this->profilePhoto, $by);
        }

        $user = $this->users->find((int) $this->editingUserId);

        return $this->updateResident->handle(
            (int) $user->resident_id,
            $this->form,
            $this->profilePhoto,
            $this->removeProfilePhoto,
            $by
        );
    }

    private function persistHousehold(Resident $resident, Admin $by): void
    {
        if (! $this->unitIsNamed()) {
            return;
        }

        $householdId = $resident->household ? (int) $resident->household->id : null;

        if ($this->unit && $this->unit['is_occupied'] && (int) $this->unit['household_id'] !== $householdId) {
            $this->addMember->handle(
                (int) $this->unit['household_id'],
                $resident->id,
                $this->household['relationship'] ?: null,
                (bool) $this->household['be_head'],
                $by
            );

            return;
        }

        $payload = [
            'property_id'  => $this->resolveProperty($by),
            'type'         => $this->household['type'] ?: HouseholdType::OWNER,
            'prev_address' => $this->household['prev_address'] ?: null,
        ];

        if ($householdId) {
            $this->updateHousehold->handle($householdId, $payload, $by);

            return;
        }

        $this->createHousehold->handle($payload + [
            'head_resident_id' => $resident->id,
            'relationship'     => $this->household['relationship'] ?: null,
        ], $by);
    }

    private function resolveProperty(Admin $by): int
    {
        if ($this->unit) {
            return (int) $this->unit['property_id'];
        }

        return $this->createProperty->handle([
            'block'      => (int) $this->property['block'],
            'lot'        => (int) $this->property['lot'],
            'address_id' => $this->units->associationAddress()->id,
            'sqm'        => $this->property['sqm'] ?: null,
            'type'       => $this->property['type'] ?: null,
        ], null, $by)->id;
    }

    private function persistAccount(Resident $resident, Admin $by): void
    {
        $data = [
            'email'     => $this->form['email'],
            'is_active' => $this->form['is_active'] ?? true,
        ];

        if ($this->editingUserId) {
            $this->updateAccount->handle((int) $this->editingUserId, $data, $by);

            return;
        }

        $this->createAccount->handle(['resident_id' => $resident->id] + $data, $by);
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.admin.homeowners.homeowner.modals.homeowner-form-modal',
            [
                'editing'              => $this->editingUserId !== null,
                'canJoin'              => $this->unitCanJoin(),
                'canChangeTenure'      => $this->unitCanChangeTenure(),
                'note'                 => $this->unitNote(),
                'showSpouse'           => CivilStatus::isMarried((string) ($this->form['civil_status'] ?? '')),
                'householdTypeOptions' => $this->loaded ? HouseholdType::labels() : [],
                'propertyTypeOptions'  => $this->loaded ? PropertyType::labels() : [],
                'address'              => $this->loaded ? $this->units->associationAddress() : null,
            ] + ($this->loaded ? $this->formQuery->options() : $this->formQuery->blank())
        );
    }
}
