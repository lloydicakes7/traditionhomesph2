<?php

namespace App\Http\Livewire\Admin\Homeowners\Property\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Property;
use App\Queries\Homeowners\Properties\PropertyListQuery;

/**
 * "Property Details" modal — read-only summary.
 *
 * Opened by native Bootstrap JS from the table's view button (carries
 * `data-id`); `modal-loader.js` emits `loadPropertyViewModal`, which loads the
 * record through PropertyListQuery::find(). Read-only — emits nothing.
 */
class PropertyViewModal extends AdminModalComponent
{
    const MODAL_ID = 'propertyViewModal';

    /** @var \App\Models\Property|null */
    public $viewProperty = null;

    protected $listeners = [
        'loadPropertyViewModal'  => 'load',
        'closePropertyViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PropertyListQuery */
    protected $query;

    public function boot(PropertyListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', Property::class);

        $this->viewProperty = $this->query->find((int) $id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewProperty = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.property.modals.property-view-modal');
    }
}
