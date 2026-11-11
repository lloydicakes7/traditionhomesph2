<?php

namespace App\Http\Livewire\Admin\Homeowners\Property\Modals;

use App\Actions\Homeowners\Properties\DeleteProperties;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Property;
use App\Queries\Homeowners\Properties\PropertyListQuery;

/**
 * "Delete Property" confirmation modal.
 *
 * Opened by native Bootstrap JS from the table's delete button (carries
 * `data-id`); `modal-loader.js` emits `loadPropertyDeleteModal`. Deletes
 * through DeleteProperties and emits `property-saved` so the table and stats
 * refresh.
 */
class PropertyDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'propertyDeleteModal';

    /** @var int|null Property being deleted */
    public $deletingPropertyId = null;

    /** @var string|null Record summary shown in the dialog */
    public $deletingPropertyLabel = null;

    protected $listeners = [
        'loadPropertyDeleteModal'  => 'load',
        'closePropertyDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PropertyListQuery */
    protected $query;

    /** @var DeleteProperties */
    protected $deleteProperties;

    public function boot(PropertyListQuery $query, DeleteProperties $deleteProperties)
    {
        $this->query = $query;
        $this->deleteProperties = $deleteProperties;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Property::class);

        $property = $this->query->find((int) $id);

        $this->deletingPropertyId = $property->id;
        $this->deletingPropertyLabel = $property->block_lot_label;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingPropertyId = null;
        $this->deletingPropertyLabel = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingPropertyId) {
            return;
        }

        $this->authorize('delete', Property::class);
        $this->deleteProperties->handle($this->deletingPropertyId, auth('web')->user());

        $this->emit('notify', 'success', 'Property deleted successfully.');
        $this->emit('property-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.property.modals.property-delete-modal');
    }
}
