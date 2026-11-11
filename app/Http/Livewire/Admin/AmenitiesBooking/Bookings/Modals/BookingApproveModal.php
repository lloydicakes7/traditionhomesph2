<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\ApproveBooking;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Approve Booking — the confirmation a pending row's Approve action opens.
 *
 * Opened client-side by Bootstrap (`#bookingApproveModal`) with the booking's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Approving is
 * the office's own step: the schedule is held for the homeowner from the moment
 * it is taken, so the move is confirmed before it is made.
 *
 * The dialog asks the reservation whether the move still applies, rather than
 * trusting the page it was opened from — a page left open while the booking
 * moved on cannot approve it a second time.
 */
class BookingApproveModal extends AdminModalComponent
{
    const MODAL_ID = 'bookingApproveModal';

    /** @var int|null Reservation the confirmation applies to */
    public $approvingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingApproveModal'  => 'load',
        'closeBookingApproveModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var ApproveBooking */
    protected $approveBooking;

    public function boot(AmenityBookingListQuery $bookings, ApproveBooking $approveBooking)
    {
        $this->bookings = $bookings;
        $this->approveBooking = $approveBooking;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('approve', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        if (! $booking->can_be_approved) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->approvingBookingId = $booking->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->approvingBookingId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Approve
    // =====================================================================

    public function approve(): void
    {
        if (! $this->loaded || ! $this->approvingBookingId) {
            return;
        }

        $this->authorize('approve', AmenityBooking::class);

        $booking = $this->approveBooking->handle(
            $this->bookings->find($this->approvingBookingId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Booking for the ' . $booking->amenity->name . ' approved.');
        $this->emit('booking-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-approve-modal', [
            'booking' => $this->approvingBookingId
                ? $this->bookings->detail($this->approvingBookingId)
                : null,
        ]);
    }
}
