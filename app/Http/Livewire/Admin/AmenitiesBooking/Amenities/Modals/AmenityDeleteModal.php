<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Amenities\Modals;

use App\Actions\Amenities\DeleteAmenities;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\Amenity;
use App\Queries\Amenities\AmenityListQuery;

class AmenityDeleteModal extends AdminModalComponent
{
    public $deletingAmenityId = null;

    protected $listeners = [
        'loadAmenityDeleteModal'  => 'load',
        'closeAmenityDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AmenityListQuery */
    protected $amenities;

    /** @var DeleteAmenities */
    protected $deleteAmenities;

    public function boot(AmenityListQuery $amenities, DeleteAmenities $deleteAmenities)
    {
        $this->amenities = $amenities;
        $this->deleteAmenities = $deleteAmenities;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Amenity::class);

        $bookings = $this->amenities->bookingCount($id);

        if ($bookings > 0) {
            $amenity = $this->amenities->detail($id);

            $this->emit('notify', 'warning', sprintf(
                'The %s has %d %s on file, so it cannot be deleted. Cancel them first, or keep the amenity.',
                $amenity->name,
                $bookings,
                $bookings === 1 ? 'booking' : 'bookings'
            ));

            $this->dispatchBrowserEvent('modal-close', ['id' => 'amenityDeleteModal']);

            return;
        }

        $this->deletingAmenityId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingAmenityId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAmenityId) {
            return;
        }

        $this->authorize('delete', Amenity::class);

        $removed = $this->deleteAmenities->handle([$this->deletingAmenityId], auth('web')->user());

        if ($removed === 0) {
            $this->emit('notify', 'warning', 'That amenity has bookings on file, so it was not deleted.');
            return;
        }

        $this->deletingAmenityId = null;

        $this->emit('notify', 'success', 'Amenity deleted successfully.');
        $this->emit('amenity-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'amenityDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.amenities.modals.amenity-delete-modal', [
            'deletingAmenity' => $this->deletingAmenityId
                ? $this->amenities->detail($this->deletingAmenityId)
                : null,
        ]);
    }
}
