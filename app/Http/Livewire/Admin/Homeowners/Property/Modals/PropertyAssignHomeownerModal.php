<?php

namespace App\Http\Livewire\Admin\Homeowners\Property\Modals;

use App\Actions\Homeowners\Households\CreateHousehold;
use App\Enums\HouseholdType;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Property;
use App\Queries\Homeowners\Properties\PropertyFormQuery;
use Illuminate\Validation\Rule;

class PropertyAssignHomeownerModal extends AdminModalComponent
{
    const MODAL_ID = 'propertyAssignHomeownerModal';

    public $quickPropertyId = null;

    public $assignForm = [
        'head_resident_id' => '',
        'type'             => 'owner',
        'relationship'     => '',
        'prev_address'     => '',
    ];

    protected $listeners = [
        'loadPropertyAssignHomeownerModal'  => 'load',
        'closePropertyAssignHomeownerModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var PropertyFormQuery */
    protected $query;

    /** @var CreateHousehold */
    protected $createHousehold;

    public function boot(PropertyFormQuery $query, CreateHousehold $createHousehold)
    {
        $this->query = $query;
        $this->createHousehold = $createHousehold;
    }

    // ===== Rules =====

    protected function assignRules(): array
    {
        return [
            'assignForm.head_resident_id' => ['required', 'exists:residents,id'],
            'assignForm.type'             => ['required', Rule::in(HouseholdType::values())],
            'assignForm.relationship'     => ['required', 'string', 'max:100'],
            'assignForm.prev_address'     => ['nullable', 'string', 'max:500'],
        ];
    }

    // ===== Load =====

    public function load($propertyId): void
    {
        if (! $propertyId) {
            return;
        }

        $this->authorize('update', Property::class);

        $this->quickPropertyId = (int) $propertyId;
        $this->assignForm = $this->blankForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->quickPropertyId = null;

        parent::resetModal();
    }

    // ===== Save =====

    public function saveAssignHomeowner(): void
    {
        if (! $this->loaded || ! $this->quickPropertyId) {
            return;
        }

        $this->authorize('update', Property::class);
        $this->validate($this->assignRules());

        $payload = $this->assignForm;
        $payload['property_id'] = $this->quickPropertyId;

        try {
            $this->createHousehold->handle($payload, auth('web')->user());
        } catch (\RuntimeException $e) {
            $this->addError('assignForm.head_resident_id', $e->getMessage());
            $this->emit('notify', 'error', $e->getMessage());

            return;
        }

        $this->emit('notify', 'success', 'Homeowner assigned successfully.');
        $this->emit('property-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Helpers =====

    private function blankForm(): array
    {
        return [
            'head_resident_id' => '',
            'type'             => 'owner',
            'relationship'     => '',
            'prev_address'     => '',
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.admin.homeowners.property.modals.property-assign-homeowner-modal',
            $this->loaded ? $this->query->options() : $this->query->blank()
        );
    }
}
