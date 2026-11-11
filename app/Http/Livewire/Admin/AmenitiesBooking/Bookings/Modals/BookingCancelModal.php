<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\CancelBooking;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Cancel Booking — the confirmation a row's Cancel action opens.
 *
 * Opened client-side by Bootstrap (`#bookingCancelModal`) with the booking's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. The
 * reservation is withdrawn and the window it was holding is released, so the
 * move is confirmed before it is made.
 *
 * Cancelling is the office's move, and the remark it may carry is what the
 * homeowner reads as the reason — optional, because an officer may simply be
 * releasing a window they cannot hold.
 */
class BookingCancelModal extends AdminModalComponent
{
    const MODAL_ID = 'bookingCancelModal';

    /** @var int|null Reservation the confirmation applies to */
    public $cancellingBookingId = null;

    /** @var string Reason the reservation is being cancelled */
    public $remarks = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingCancelModal'  => 'load',
        'closeBookingCancelModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var CancelBooking */
    protected $cancelBooking;

    public function boot(AmenityBookingListQuery $bookings, CancelBooking $cancelBooking)
    {
        $this->bookings = $bookings;
        $this->cancelBooking = $cancelBooking;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('cancel', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        if (! $booking->can_be_cancelled) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->cancellingBookingId = $booking->id;
        $this->remarks = '';

        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->cancellingBookingId = null;
        $this->remarks = '';

        parent::resetModal();
    }

    // =====================================================================
    // Cancel
    // =====================================================================

    public function cancel(): void
    {
        if (! $this->loaded || ! $this->cancellingBookingId) {
            return;
        }

        $this->authorize('cancel', AmenityBooking::class);

        $this->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = $this->cancelBooking->handle(
            $this->bookings->find($this->cancellingBookingId),
            $this->remarks ?: null,
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Booking for the ' . $booking->amenity->name . ' cancelled.');
        $this->emit('booking-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-cancel-modal', [
            'booking' => $this->cancellingBookingId
                ? $this->bookings->detail($this->cancellingBookingId)
                : null,
        ]);
    }
}
