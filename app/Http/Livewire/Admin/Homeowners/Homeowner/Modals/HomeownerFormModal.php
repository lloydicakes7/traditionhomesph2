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
use App\Http\Livewire\AdminModalComponent;
use App\Http\Requests\Homeowners\ResidentRequest;
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
    use WithFileUploads;

    const MODAL_ID = 'homeownerFormModal';

    /** @var int|null Account being edited; null while creating */
    public $editingUserId = null;

    /** @var array The account and profile fields, in one form */
    public $form = [];

    /** @var array<string, mixed> The unit and household the homeowner heads */
    public $household = [
        'block'          => '',
        'lot'            => '',
        'type'           => HouseholdType::OWNER,
        'prev_address'   => '',
        'link_household' => false,
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
        return [
            'form.email'               => [
                'required', 'email', 'max:255',
                'unique:users,email,' . ($this->editingUserId ?? 'NULL') . ',id',
            ],
            'form.is_active'           => ['boolean'],
            'profilePhoto'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'household.block'          => [$this->editingUserId ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'household.lot'            => [$this->editingUserId ? 'nullable' : 'required', 'integer', 'min:1', 'max:9999'],
            'household.type'           => ['required', Rule::in(HouseholdType::values())],
            'household.prev_address'   => ['nullable', 'string', 'max:500'],
            'household.link_household' => ['boolean'],
        ] + ResidentRequest::rulesFor('form');
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
        $this->household = [
            'block'          => '',
            'lot'            => '',
            'type'           => HouseholdType::OWNER,
            'prev_address'   => '',
            'link_household' => false,
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
        if ($name !== 'household.block' && $name !== 'household.lot') {
            return;
        }

        $this->household['link_household'] = false;
        $this->unit = null;
        $this->resetErrorBag();

        if (! $this->unitIsNamed()) {
            return;
        }

        $this->unit = $this->units->atUnit((int) $this->household['block'], (int) $this->household['lot']);
    }

    // ===== Actions =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorizeSave();
        $this->validate();

        if (! $this->addressIsResolvable()) {
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

        $this->household = [
            'block'          => $property ? (string) $property->block : '',
            'lot'            => $property ? (string) $property->lot : '',
            'type'           => $household?->type ?: HouseholdType::OWNER,
            'prev_address'   => $household?->prev_address ?? '',
            'link_household' => false,
        ];

        $this->householdId = $household ? (int) $household->id : null;
        $this->unit = $property
            ? $this->units->atUnit((int) $property->block, (int) $property->lot)
            : null;
    }

    private function unitIsNamed(): bool
    {
        return (int) ($this->household['block'] ?? 0) > 0 && (int) ($this->household['lot'] ?? 0) > 0;
    }

    private function unitIsOwnHousehold(): bool
    {
        return $this->householdId !== null
            && $this->unit !== null
            && (int) $this->unit['household_id'] === $this->householdId;
    }

    /**
     * Refuse a unit held by another household, and a link the officer did not
     * confirm. An unnamed unit is only allowed while editing: the homeowner then
     * has no household on file yet.
     */
    private function addressIsResolvable(): bool
    {
        if (! $this->unitIsNamed()) {
            $this->unit = null;

            return (bool) $this->editingUserId;
        }

        $block = (int) $this->household['block'];
        $lot = (int) $this->household['lot'];

        $this->unit = $this->units->atUnit($block, $lot);

        if ($this->unit && $this->unit['is_occupied'] && ! $this->unitIsOwnHousehold()) {
            if ($this->householdId !== null) {
                $this->addError('household.block', 'Another household already lives at this address. Choose another unit.');

                return false;
            }

            if (empty($this->household['link_household'])) {
                $this->addError(
                    'household.link_household',
                    'Confirm that the homeowner lives in the household at this address, or choose another unit.'
                );

                return false;
            }
        }

        if (! $this->unit && $this->units->associationAddress() === null) {
            $this->addError('household.block', 'The association address is not recorded.');

            return false;
        }

        return true;
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
            $this->addMember->handle((int) $this->unit['household_id'], $resident->id, null, false, $by);

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
            'relationship'     => null,
        ], $by);
    }

    private function resolveProperty(Admin $by): int
    {
        if ($this->unit) {
            return (int) $this->unit['property_id'];
        }

        return $this->createProperty->handle([
            'block'      => (int) $this->household['block'],
            'lot'        => (int) $this->household['lot'],
            'address_id' => $this->units->associationAddress()->id,
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
                'editing'            => $this->editingUserId !== null,
                'ownHousehold'       => $this->unitIsOwnHousehold(),
                'showSpouse'         => CivilStatus::isMarried((string) ($this->form['civil_status'] ?? '')),
                'householdTypes'     => $this->loaded ? HouseholdType::labels() : [],
                'associationAddress' => $this->loaded ? $this->units->associationAddress() : null,
            ] + ($this->loaded ? $this->formQuery->options() : $this->formQuery->blank())
        );
    }
}
