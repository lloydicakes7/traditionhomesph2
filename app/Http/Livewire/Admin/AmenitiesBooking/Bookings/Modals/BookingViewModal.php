<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * "View Booking" modal — one reservation, tracked from submission through
 * approval, payment, deposit and completion.
 *
 * Opens instantly through Bootstrap (`#bookingViewModal`, triggered with
 * `data-toggle` + `data-id` from the bookings table, the calendar's reservation
 * chips and the portal alike); the reservation is fetched lazily afterwards,
 * when `public/js/modal-loader.js` fires `loadBookingViewModal`. Shared by every
 * surface that offers it, so a reservation reads the same wherever it is opened.
 * Read-only.
 */
class BookingViewModal extends AdminModalComponent
{
    /** @var \App\Models\Amenity\AmenityBooking|null Reservation being viewed */
    public $viewBooking = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingViewModal'  => 'load',
        'closeBookingViewModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    public function boot(AmenityBookingListQuery $bookings)
    {
        $this->bookings = $bookings;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', AmenityBooking::class);

        $this->viewBooking = $this->bookings->detail($id);

        $this->markLoaded();
    }

    /**
     * Drop the reservation when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->viewBooking = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-view-modal');
    }
}
