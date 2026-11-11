<?php

namespace App\Http\Livewire\Portal\Bookings\Modals;

use App\Actions\Amenities\Bookings\DeleteOwnBooking;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Delete Booking — the confirmation the portal's own Delete action opens.
 *
 * Opened client-side by Bootstrap (`#bookingDeleteModal`) with the booking's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Removing a
 * request takes the record off the homeowner's own list; it is not a
 * cancellation, which the office makes with a remark for the homeowner.
 *
 * A homeowner removes only their own reservation, and only while the office has
 * not invoiced it — AmenityBookingPolicy::deleteOwn, checked as the dialog loads
 * and again when it is confirmed, exactly as the office's own moves are.
 */
class BookingDeleteModal extends PortalModalComponent
{
    const MODAL_ID = 'bookingDeleteModal';

    /** @var int|null Reservation the confirmation applies to */
    public $deletingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingDeleteModal'  => 'load',
        'closeBookingDeleteModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var DeleteOwnBooking */
    protected $deleteBooking;

    public function boot(AmenityBookingListQuery $bookings, DeleteOwnBooking $deleteBooking)
    {
        $this->bookings = $bookings;
        $this->deleteBooking = $deleteBooking;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $booking = $this->bookings->detail($id);

        $this->authorize('deleteOwn', $booking);

        $this->deletingBookingId = $booking->id;

        $this->markLoaded();
    }

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

        $booking = $this->bookings->find($this->deletingBookingId);

        $this->authorize('deleteOwn', $booking);

        $this->deleteBooking->handle($booking);

        $this->emit('notify', 'success', 'Booking deleted.');
        $this->emit('booking-deleted');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.bookings.modals.booking-delete-modal', [
            'booking' => $this->deletingBookingId
                ? $this->bookings->detail($this->deletingBookingId)
                : null,
        ]);
    }
}
