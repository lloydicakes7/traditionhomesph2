<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\DeleteBookings;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * "Delete Booking" confirmation modal.
 *
 * Opens instantly through Bootstrap (`#bookingDeleteModal`, triggered from the
 * table's actions menu with `data-toggle` + `data-id`); the reservation is
 * fetched lazily afterwards, when `public/js/modal-loader.js` fires
 * `loadBookingDeleteModal`. Deletes through DeleteBookings and emits
 * `booking-saved` so the table refreshes. Deleting takes the reservation off the
 * books entirely — cancelling it is the move that keeps the record.
 */
class BookingDeleteModal extends AdminModalComponent
{
    /** @var int|null Reservation being deleted */
    public $deletingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingDeleteModal'  => 'load',
        'closeBookingDeleteModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var DeleteBookings */
    protected $deleteBookings;

    public function boot(AmenityBookingListQuery $bookings, DeleteBookings $deleteBookings)
    {
        $this->bookings = $bookings;
        $this->deleteBookings = $deleteBookings;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', AmenityBooking::class);

        $this->bookings->detail($id);

        $this->deletingBookingId = $id;

        $this->markLoaded();
    }

    /**
     * Drop the reservation when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->deletingBookingId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingBookingId) {
            return;
        }

        $this->authorize('delete', AmenityBooking::class);

        $this->deleteBookings->handle([$this->deletingBookingId], auth('web')->user());

        $this->deletingBookingId = null;

        $this->emit('notify', 'success', 'Booking deleted successfully.');
        $this->emit('booking-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'bookingDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-delete-modal', [
            'deletingBooking' => $this->deletingBookingId
                ? $this->bookings->detail($this->deletingBookingId)
                : null,
        ]);
    }
}
