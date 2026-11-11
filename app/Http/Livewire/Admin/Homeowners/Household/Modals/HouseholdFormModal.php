<?php

namespace App\Http\Livewire\Admin\Homeowners\Household\Modals;

use App\Actions\Homeowners\Households\CreateHousehold;
use App\Actions\Homeowners\Households\UpdateHousehold;
use App\Enums\HouseholdType;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Household\Household;
use App\Queries\Homeowners\Households\HouseholdFormQuery;
use App\Queries\Homeowners\Households\HouseholdListQuery;
use Illuminate\Validation\Rule;

/**
 * "Add / Edit Household" modal — one form for both flows.
 *
 * A household names its people, not an account: the officer picks the resident
 * who heads the family and how that head is related to them. The head becomes
 * the household's `household_members` head row, and the RBI composition is read
 * from the members afterwards.
 *
 * Opened by native Bootstrap JS: the table's button carries `data-toggle` and,
 * for edit, `data-id`. `modal-loader.js` turns that into
 * `openHouseholdFormModal` or `loadHouseholdFormModal`; `household-saved`
 * refreshes the list.
 */
class HouseholdFormModal extends AdminModalComponent
{
    const MODAL_ID = 'householdFormModal';

    /** @var bool Whether editing an existing household */
    public $isEditing = false;

    /** @var int|null ID of household being edited */
    public $editingHouseholdId = null;

    /** @var array Household form fields */
    public $form = [
        'property_id'      => '',
        'type'             => 'owner',
        'head_resident_id' => '',
        'relationship'     => '',
        'prev_address'     => '',
    ];

    protected $listeners = [
        'openHouseholdFormModal'  => 'openCreate',
        'loadHouseholdFormModal'  => 'openEdit',
        'closeHouseholdFormModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var HouseholdFormQuery */
    protected $query;

    /** @var HouseholdListQuery */
    protected $households;

    /** @var CreateHousehold */
    protected $createHousehold;

    /** @var UpdateHousehold */
    protected $updateHousehold;

    public function boot(
        HouseholdFormQuery $query,
        HouseholdListQuery $households,
        CreateHousehold $createHousehold,
        UpdateHousehold $updateHousehold
    ) {
        $this->query = $query;
        $this->households = $households;
        $this->createHousehold = $createHousehold;
        $this->updateHousehold = $updateHousehold;
    }

    // ===== Rules =====

    protected function rules(): array
    {
        return [
            'form.property_id'      => ['required', 'exists:properties,id'],
            'form.type'             => ['required', Rule::in(HouseholdType::values())],
            'form.head_resident_id' => ['required', 'exists:residents,id'],
            'form.relationship'     => ['required', 'string', 'max:100'],
            'form.prev_address'     => ['nullable', 'string', 'max:500'],
        ];
    }

    // ===== Open / Close =====

    public function openCreate(): void
    {
        $this->authorize('create', Household::class);

        $this->isEditing = false;
        $this->editingHouseholdId = null;
        $this->form = $this->blankForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Household::class);

        $household = $this->households->find((int) $id);
        $head = $household->headMember;

        $this->isEditing = true;
        $this->editingHouseholdId = $household->id;
        $this->form = [
            'property_id'      => (string) $household->property_id,
            'type'             => $household->type,
            'head_resident_id' => (string) ($head?->resident_id ?? ''),
            'relationship'     => $head?->relationship ?? '',
            'prev_address'     => $household->prev_address ?? '',
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingHouseholdId = null;
        $this->form = $this->blankForm();
        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Save =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Household::class);
        $this->validate();

        try {
            if ($this->isEditing) {
                $this->updateHousehold->handle($this->editingHouseholdId, $this->form, auth('web')->user());
            } else {
                $this->createHousehold->handle($this->form, auth('web')->user());
            }
        } catch (\RuntimeException $e) {
            $this->addError('form.property_id', $e->getMessage());
            $this->emit('notify', 'error', $e->getMessage());

            return;
        }

        $this->emit('notify', 'success', $this->isEditing
            ? 'Household updated successfully.'
            : 'Household created successfully.');

        $this->emit('household-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Helpers =====

    private function blankForm(): array
    {
        return [
            'property_id'      => '',
            'type'             => 'owner',
            'head_resident_id' => '',
            'relationship'     => '',
            'prev_address'     => '',
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.admin.homeowners.household.modals.household-form-modal',
            $this->loaded ? $this->query->options($this->editingHouseholdId) : $this->query->blank()
        );
    }
}
