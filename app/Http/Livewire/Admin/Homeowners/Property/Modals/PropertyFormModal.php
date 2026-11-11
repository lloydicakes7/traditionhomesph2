<?php

namespace App\Http\Livewire\Admin\Homeowners\Property\Modals;

use App\Actions\Homeowners\Properties\CreateProperty;
use App\Actions\Homeowners\Properties\UpdateProperty;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Address;
use App\Models\Property;
use App\Queries\Homeowners\Properties\PropertyFormQuery;
use App\Queries\Homeowners\Properties\PropertyListQuery;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class PropertyFormModal extends AdminModalComponent
{
    use WithFileUploads;

    const MODAL_ID = 'propertyFormModal';

    /** @var bool Whether editing an existing property */
    public $isEditing = false;

    /** @var int|null ID of property being edited */
    public $editingPropertyId = null;

    /** @var array Property form fields */
    public $form = [
        'block'      => '',
        'lot'        => '',
        'address_id' => '',
        'sqm'        => '',
        'type'       => '',
    ];

    /** @var mixed Uploaded property picture (temporary) */
    public $propertyPicture;

    /** @var string|null Existing photo URL for the edit form preview */
    public $existingPropertyPhotoUrl = null;

    /** @var bool Whether to remove the existing picture on save */
    public $removePropertyPicture = false;

    protected $listeners = [
        'openPropertyFormModal'  => 'openCreate',
        'loadPropertyFormModal'  => 'load',
        'closePropertyFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PropertyListQuery */
    protected $query;

    /** @var PropertyFormQuery */
    protected $formQuery;

    /** @var CreateProperty */
    protected $createProperty;

    /** @var UpdateProperty */
    protected $updateProperty;

    public function boot(
        PropertyListQuery $query,
        PropertyFormQuery $formQuery,
        CreateProperty $createProperty,
        UpdateProperty $updateProperty
    ) {
        $this->query = $query;
        $this->formQuery = $formQuery;
        $this->createProperty = $createProperty;
        $this->updateProperty = $updateProperty;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.block'      => ['required', 'integer', 'min:1'],
            'form.lot'        => ['required', 'integer', 'min:1',
                Rule::unique('properties', 'lot')
                    ->where(fn ($query) => $query->where('block', $this->form['block']))
                    ->ignore($this->editingPropertyId),
            ],
            'form.address_id' => ['required', 'exists:addresses,id'],
            'form.sqm'        => ['nullable', 'numeric', 'min:0'],
            'form.type'       => ['nullable', 'string', Rule::in(\App\Enums\PropertyType::values())],
            'propertyPicture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Property::class);

        $primaryAddress = Address::where('is_primary', true)->first();

        $this->isEditing = false;
        $this->editingPropertyId = null;
        $this->form = [
            'block'      => '',
            'lot'        => '',
            'address_id' => $primaryAddress ? (string) $primaryAddress->id : '',
            'sqm'        => '',
            'type'       => '',
        ];
        $this->propertyPicture = null;
        $this->existingPropertyPhotoUrl = null;
        $this->removePropertyPicture = false;
        $this->resetValidation();

        $this->markLoaded();
    }

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Property::class);

        $property = $this->query->find((int) $id);

        $this->isEditing = true;
        $this->editingPropertyId = $property->id;
        $this->form = [
            'block'      => $property->block !== null ? (string) $property->block : '',
            'lot'        => $property->lot !== null ? (string) $property->lot : '',
            'address_id' => (string) $property->address_id,
            'sqm'        => $property->sqm !== null ? (string) $property->sqm : '',
            'type'       => $property->type ?? '',
        ];
        $this->propertyPicture = null;
        $this->existingPropertyPhotoUrl = $property->photo_url;
        $this->removePropertyPicture = false;
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingPropertyId = null;
        $this->propertyPicture = null;
        $this->existingPropertyPhotoUrl = null;
        $this->removePropertyPicture = false;
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Property::class);
        $this->validate();

        if ($this->isEditing) {
            $this->updateProperty->handle(
                $this->editingPropertyId,
                $this->form,
                $this->propertyPicture,
                $this->removePropertyPicture,
                auth('web')->user()
            );
        } else {
            $this->createProperty->handle($this->form, $this->propertyPicture, auth('web')->user());
        }

        $this->emit('notify', 'success', $this->isEditing ? 'Property updated successfully.' : 'Property created successfully.');
        $this->emit('property-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Picture
    // =====================================================================

    public function removePropertyPictureAction(): void
    {
        $this->authorize('update', Property::class);

        $this->removePropertyPicture = true;
        $this->existingPropertyPhotoUrl = null;
        $this->propertyPicture = null;

        $this->emit('notify', 'info', 'Property picture will be removed on save.');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view(
            'livewire.admin.homeowners.property.modals.property-form-modal',
            $this->loaded ? $this->formQuery->options() : $this->formQuery->blank()
        );
    }
}
