<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\DeleteBookings;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;

/**
 * Delete Selected Bookings — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class BookingMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'bookingMassDeleteModal';

    /** @var array Ids received from the table's selection */
    public $selectedIds = [];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingMassDeleteModal'  => 'load',
        'closeBookingMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteBookings */
    protected $deleteBookings;

    public function boot(DeleteBookings $deleteBookings)
    {
        $this->deleteBookings = $deleteBookings;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return AmenityBooking::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'bookings';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteBookings->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'booking-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-mass-delete-modal');
    }
}
