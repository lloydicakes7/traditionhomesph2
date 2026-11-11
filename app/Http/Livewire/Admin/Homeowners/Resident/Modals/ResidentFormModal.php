<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident\Modals;

use App\Actions\Homeowners\Households\AddHouseholdMember;
use App\Actions\Homeowners\Households\CreateHousehold;
use App\Actions\Homeowners\Properties\CreateProperty;
use App\Actions\Homeowners\Residents\CreateResident;
use App\Actions\Homeowners\Residents\UpdateResident;
use App\Enums\CivilStatus;
use App\Enums\HouseholdType;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Models\Admin;
use App\Models\Resident;
use App\Queries\Homeowners\Households\HouseholdUnitQuery;
use App\Queries\Homeowners\Residents\ResidentFormQuery;
use App\Queries\Homeowners\Residents\ResidentListQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

/**
 * "Add / Edit Resident" modal — one form for both flows.
 *
 * A resident joins the household already on file at the block and lot the
 * officer names, or heads a new one when the unit has no household yet. An
 * account is not required for either: the office profiles the person first and
 * issues the login separately.
 */
class ResidentFormModal extends AdminModalComponent
{
    use WithFileUploads;

    const MODAL_ID = 'residentFormModal';

    /** @var bool Whether editing an existing resident */
    public $isEditing = false;

    /** @var int|null ID of resident being edited */
    public $editingResidentId = null;

    /** @var array Resident form fields */
    public $form = [];

    /** @var array<string, mixed> The unit and household the new resident joins or heads */
    public $household = [
        'block'          => '',
        'lot'            => '',
        'relationship'   => '',
        'type'           => HouseholdType::OWNER,
        'prev_address'   => '',
        'link_household' => false,
    ];

    /** @var array<string, mixed>|null What the office holds for the named unit */
    public $unit = null;

    /** @var mixed Uploaded profile photo (temporary) */
    public $profilePhoto;

    /** @var string|null Existing photo URL for the edit form preview */
    public $existingPhotoUrl = null;

    /** @var bool Whether to remove the existing profile photo on save */
    public $removeProfilePhoto = false;

    protected $listeners = [
        'openResidentFormModal'  => 'openCreate',
        'loadResidentFormModal'  => 'load',
        'closeResidentFormModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var ResidentListQuery */
    protected $query;

    /** @var ResidentFormQuery */
    protected $formQuery;

    /** @var HouseholdUnitQuery */
    protected $units;

    /** @var CreateResident */
    protected $createResident;

    /** @var UpdateResident */
    protected $updateResident;

    /** @var AddHouseholdMember */
    protected $addMember;

    /** @var CreateHousehold */
    protected $createHousehold;

    /** @var CreateProperty */
    protected $createProperty;

    public function boot(
        ResidentListQuery $query,
        ResidentFormQuery $formQuery,
        HouseholdUnitQuery $units,
        CreateResident $createResident,
        UpdateResident $updateResident,
        AddHouseholdMember $addMember,
        CreateHousehold $createHousehold,
        CreateProperty $createProperty
    ) {
        $this->query = $query;
        $this->formQuery = $formQuery;
        $this->units = $units;
        $this->createResident = $createResident;
        $this->updateResident = $updateResident;
        $this->addMember = $addMember;
        $this->createHousehold = $createHousehold;
        $this->createProperty = $createProperty;
    }

    // ===== Validation =====

    protected function rules(): array
    {
        $rules = array_merge((new ResidentRequest)->rules(), [
            'form.birth_place' => ['nullable', 'string', 'max:255'],
            'profilePhoto'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($this->isEditing) {
            return $rules;
        }

        return array_merge($rules, [
            'household.block'          => ['required', 'integer', 'min:1', 'max:9999'],
            'household.lot'            => ['required', 'integer', 'min:1', 'max:9999'],
            'household.relationship'   => ['nullable', 'string', 'max:100'],
            'household.type'           => ['required', Rule::in(HouseholdType::values())],
            'household.prev_address'   => ['nullable', 'string', 'max:500'],
            'household.link_household' => ['boolean'],
        ]);
    }

    // ===== Load =====

    public function openCreate(): void
    {
        $this->authorize('create', Resident::class);

        $this->isEditing = false;
        $this->editingResidentId = null;
        $this->form = $this->formQuery->blankForm();
        $this->household = $this->blankHousehold();
        $this->unit = null;
        $this->profilePhoto = null;
        $this->existingPhotoUrl = null;
        $this->removeProfilePhoto = false;
        $this->resetValidation();

        $this->markLoaded();
    }

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Resident::class);

        $resident = $this->query->find((int) $id);

        $this->isEditing = true;
        $this->editingResidentId = $resident->id;
        $this->form = $this->formQuery->form($resident);
        $this->household = $this->blankHousehold();
        $this->unit = null;
        $this->existingPhotoUrl = $resident->photo_url;
        $this->profilePhoto = null;
        $this->removeProfilePhoto = false;
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingResidentId  = null;
        $this->form               = $this->formQuery->blankForm();
        $this->household          = $this->blankHousehold();
        $this->unit               = null;
        $this->profilePhoto       = null;
        $this->existingPhotoUrl   = null;
        $this->removeProfilePhoto = false;
        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Household lookup =====

    /**
     * A changed block or lot asks the office's records again, and drops the
     * confirmation given for the address the officer was leaving.
     */
    public function updated($name): void
    {
        if ($name !== 'household.block' && $name !== 'household.lot') {
            return;
        }

        $this->household['link_household'] = false;
        $this->unit = null;
        $this->resetErrorBag();

        $block = (int) ($this->household['block'] ?? 0);
        $lot = (int) ($this->household['lot'] ?? 0);

        if ($block < 1 || $lot < 1) {
            return;
        }

        $this->unit = $this->units->atUnit($block, $lot);
    }

    // ===== Save =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        if ($this->isEditing && $this->editingResidentId) {
            $this->authorize('update', Resident::class);
        } else {
            $this->authorize('create', Resident::class);
        }

        $this->validate();

        if ($this->isEditing && $this->editingResidentId) {
            $this->updateResident->handle(
                $this->editingResidentId,
                $this->form,
                $this->profilePhoto,
                $this->removeProfilePhoto,
                auth('web')->user()
            );
            $this->emit('notify', 'success', 'Resident updated successfully.');
            $this->finish();

            return;
        }

        if (! $this->addressIsResolvable()) {
            return;
        }

        $by = auth('web')->user();

        DB::transaction(function () use ($by) {
            $resident = $this->createResident->handle($this->form, $this->profilePhoto, $by);

            $this->placeResident($resident, $by);
        });

        $this->emit('notify', 'success', 'Resident created successfully.');
        $this->emit('household-saved');

        $this->finish();
    }

    private function finish(): void
    {
        $this->emit('resident-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    /**
     * The named unit is read back from the office's records. A unit already held
     * by a household needs the officer to confirm the link; a vacant or unknown
     * one becomes a household this resident heads.
     */
    private function addressIsResolvable(): bool
    {
        $block = (int) ($this->household['block'] ?? 0);
        $lot = (int) ($this->household['lot'] ?? 0);

        $this->unit = $this->units->atUnit($block, $lot);

        if ($this->unit && $this->unit['is_occupied'] && empty($this->household['link_household'])) {
            $this->addError(
                'household.link_household',
                'Confirm that this resident lives in the household at this address.'
            );

            return false;
        }

        if (! $this->unit && $this->units->associationAddress() === null) {
            $this->addError('household.block', 'The association address is not recorded.');

            return false;
        }

        return true;
    }

    private function placeResident(Resident $resident, Admin $by): void
    {
        if ($this->unit && $this->unit['is_occupied']) {
            $this->addMember->handle(
                (int) $this->unit['household_id'],
                $resident->id,
                $this->household['relationship'] ?: null,
                false,
                $by
            );

            return;
        }

        $this->createHousehold->handle([
            'property_id'      => $this->unit ? (int) $this->unit['property_id'] : $this->createUnit($by),
            'type'             => $this->household['type'] ?: HouseholdType::OWNER,
            'head_resident_id' => $resident->id,
            'relationship'     => null,
            'prev_address'     => $this->household['prev_address'] ?: null,
        ], $by);
    }

    private function createUnit(Admin $by): int
    {
        return $this->createProperty->handle([
            'block'      => (int) $this->household['block'],
            'lot'        => (int) $this->household['lot'],
            'address_id' => $this->units->associationAddress()->id,
        ], null, $by)->id;
    }

    // ===== Photo =====

    public function removePhoto(): void
    {
        $this->authorize('update', Resident::class);

        $this->removeProfilePhoto = true;
        $this->existingPhotoUrl = null;
        $this->profilePhoto = null;

        $this->emit('notify', 'info', 'Profile photo will be removed on save.');
    }

    // ===== Helpers =====

    /**
     * A resident not put in a unit yet.
     *
     * @return array<string, mixed>
     */
    private function blankHousehold(): array
    {
        return [
            'block'          => '',
            'lot'            => '',
            'relationship'   => '',
            'type'           => HouseholdType::OWNER,
            'prev_address'   => '',
            'link_household' => false,
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.admin.homeowners.resident.modals.resident-form-modal',
            [
                'showSpouse'         => CivilStatus::isMarried((string) ($this->form['civil_status'] ?? '')),
                'householdTypes'     => $this->loaded ? HouseholdType::labels() : [],
                'associationAddress' => $this->units->associationAddress(),
            ] + ($this->loaded ? $this->formQuery->options() : $this->formQuery->blank())
        );
    }
}
