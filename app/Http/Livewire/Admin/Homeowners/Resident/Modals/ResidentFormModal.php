<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident\Modals;

use App\Actions\Homeowners\Households\AddHouseholdMember;
use App\Actions\Homeowners\Households\CreateHousehold;
use App\Actions\Homeowners\Properties\CreateProperty;
use App\Actions\Homeowners\Residents\CreateResident;
use App\Actions\Homeowners\Residents\UpdateResident;
use App\Enums\CivilStatus;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\ValidatesSectoralTags;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Rules\HouseholdHeadAge;
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
    use ValidatesSectoralTags, WithFileUploads;

    const MODAL_ID = 'residentFormModal';

    /** @var bool Whether editing an existing resident */
    public $isEditing = false;

    /** @var int|null ID of resident being edited */
    public $editingResidentId = null;

    /** @var array Resident form fields */
    public $form = [];

    /** @var array<string, mixed> The unit the resident joins or heads */
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
        $rules = array_merge((new ResidentRequest)->rules(), $this->sectoralTagRules('form'), [
            'form.birth_place' => ['nullable', 'string', 'max:255'],
            'profilePhoto'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($this->isEditing) {
            return $rules;
        }

        if ($this->residentBecomesHead()) {
            $rules['form.birth_date'] = array_merge($rules['form.birth_date'], [new HouseholdHeadAge]);
        }

        return array_merge($rules, [
            'property.block'         => ['required', 'integer', 'min:1', 'max:9999'],
            'property.lot'           => ['required', 'integer', 'min:1', 'max:9999'],
            'property.sqm'           => [
                Rule::requiredIf(function () {
                    return $this->householdIsNewUnit();
                }),
                'nullable', 'numeric', 'min:0', 'max:99999',
            ],
            'property.type'          => [
                Rule::requiredIf(function () {
                    return $this->householdIsNewUnit();
                }),
                'nullable', Rule::in(PropertyType::values()),
            ],
            'household.relationship' => ['nullable', 'string', 'max:100'],
            'household.type'         => ['required', Rule::in(HouseholdType::values())],
            'household.prev_address' => ['nullable', 'string', 'max:500'],
            'household.be_head'      => ['boolean'],
        ]);
    }

    // ===== Load =====

    public function openCreate(): void
    {
        $this->authorize('create', Resident::class);

        $this->isEditing = false;
        $this->editingResidentId = null;
        $this->form = $this->formQuery->blankForm();
        $this->property = $this->blankProperty();
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
        $this->property = $this->blankProperty();
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
        $this->property           = $this->blankProperty();
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

    private function unitIsNamed(): bool
    {
        return (int) ($this->property['block'] ?? 0) > 0 && (int) ($this->property['lot'] ?? 0) > 0;
    }

    private function householdIsNewUnit(): bool
    {
        return $this->unitIsNamed() && $this->unit === null;
    }

    private function residentBecomesHead(): bool
    {
        if ($this->isEditing) {
            return false;
        }

        return (bool) ($this->household['be_head'] ?? false)
            || $this->unit === null
            || ! ($this->unit['is_occupied'] ?? false);
    }

    private function unitNote(): string
    {
        if (! $this->unitIsNamed()) {
            return __('households.note.name_unit');
        }

        if ($this->unit === null) {
            return __('households.note.new_unit');
        }

        return $this->unit['is_occupied']
            ? __('households.note.occupied')
            : __('households.note.vacant');
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

        $this->refreshUnit();
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

        if (! $this->unitIsResolvable()) {
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
     * A new unit is created under the association's own address, so that
     * address must be on file before anything is written.
     */
    private function unitIsResolvable(): bool
    {
        if (! $this->unit && $this->units->associationAddress() === null) {
            $this->addError('property.block', __('households.note.no_association_address'));

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
                (bool) $this->household['be_head'],
                $by
            );

            return;
        }

        $this->createHousehold->handle([
            'property_id'      => $this->unit ? (int) $this->unit['property_id'] : $this->createUnit($by),
            'type'             => $this->household['type'] ?: HouseholdType::OWNER,
            'head_resident_id' => $resident->id,
            'relationship'     => $this->household['relationship'] ?: null,
            'prev_address'     => $this->household['prev_address'] ?: null,
        ], $by);
    }

    private function createUnit(Admin $by): int
    {
        return $this->createProperty->handle([
            'block'      => (int) $this->property['block'],
            'lot'        => (int) $this->property['lot'],
            'address_id' => $this->units->associationAddress()->id,
            'sqm'        => $this->property['sqm'] ?: null,
            'type'       => $this->property['type'] ?: null,
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
     * A unit not named yet.
     *
     * @return array<string, mixed>
     */
    private function blankProperty(): array
    {
        return [
            'block' => '',
            'lot'   => '',
            'sqm'   => '',
            'type'  => '',
        ];
    }

    /**
     * A resident not put in a unit yet.
     *
     * @return array<string, mixed>
     */
    private function blankHousehold(): array
    {
        return [
            'type'         => HouseholdType::OWNER,
            'prev_address' => '',
            'relationship' => '',
            'be_head'      => false,
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.admin.homeowners.resident.modals.resident-form-modal',
            [
                'showSpouse'           => CivilStatus::isMarried((string) ($this->form['civil_status'] ?? '')),
                'householdTypeOptions' => $this->loaded ? HouseholdType::labels() : [],
                'propertyTypeOptions'  => $this->loaded ? PropertyType::labels() : [],
                'address'              => $this->units->associationAddress(),
                'note'                 => $this->unitNote(),
            ] + ($this->loaded ? $this->formQuery->options() : $this->formQuery->blank())
        );
    }
}
