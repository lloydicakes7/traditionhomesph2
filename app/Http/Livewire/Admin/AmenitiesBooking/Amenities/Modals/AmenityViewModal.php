<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Amenities\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\Amenity;
use App\Queries\Amenities\AmenityListQuery;

class AmenityViewModal extends AdminModalComponent
{
    public $viewAmenity = null;

    protected $listeners = [
        'loadAmenityViewModal'  => 'load',
        'closeAmenityViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AmenityListQuery */
    protected $amenities;

    public function boot(AmenityListQuery $amenities)
    {
        $this->amenities = $amenities;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', Amenity::class);

        $this->viewAmenity = $this->amenities->detail($id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewAmenity = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.amenities.modals.amenity-view-modal');
    }
}
