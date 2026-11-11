<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\CompleteBooking;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Complete Booking — the confirmation a row's Complete action opens.
 *
 * Opened client-side by Bootstrap (`#bookingCompleteModal`) with the booking's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. Closing a
 * reservation as used releases the window it was holding, so the move is
 * confirmed before it is made.
 *
 * A reservation that costs nothing reaches this step straight from being
 * approved — there is no payment to wait for — which is the reservation's own
 * rule (`can_be_completed`), asked again here.
 */
class BookingCompleteModal extends AdminModalComponent
{
    const MODAL_ID = 'bookingCompleteModal';

    /** @var int|null Reservation the confirmation applies to */
    public $completingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingCompleteModal'  => 'load',
        'closeBookingCompleteModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var CompleteBooking */
    protected $completeBooking;

    public function boot(AmenityBookingListQuery $bookings, CompleteBooking $completeBooking)
    {
        $this->bookings = $bookings;
        $this->completeBooking = $completeBooking;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('complete', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        if (! $booking->can_be_completed) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->completingBookingId = $booking->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->completingBookingId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Complete
    // =====================================================================

    public function complete(): void
    {
        if (! $this->loaded || ! $this->completingBookingId) {
            return;
        }

        $this->authorize('complete', AmenityBooking::class);

        $booking = $this->completeBooking->handle(
            $this->bookings->find($this->completingBookingId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Booking for the ' . $booking->amenity->name . ' completed.');
        $this->emit('booking-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-complete-modal', [
            'booking' => $this->completingBookingId
                ? $this->bookings->detail($this->completingBookingId)
                : null,
        ]);
    }
}
