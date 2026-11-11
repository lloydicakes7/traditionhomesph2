<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\RefundDeposit;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Refund Deposit — the confirmation a row's Refund Deposit action opens.
 *
 * Opened client-side by Bootstrap (`#bookingRefundDepositModal`) with the
 * booking's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * The deposit is returned to the homeowner after inspection, so the move is
 * confirmed before it is made.
 *
 * Only a reservation whose deposit is still held offers the move
 * (`deposit_is_open`), which the dialog asks again.
 */
class BookingRefundDepositModal extends AdminModalComponent
{
    const MODAL_ID = 'bookingRefundDepositModal';

    /** @var int|null Reservation the confirmation applies to */
    public $refundingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingRefundDepositModal'  => 'load',
        'closeBookingRefundDepositModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var RefundDeposit */
    protected $refundDeposit;

    public function boot(AmenityBookingListQuery $bookings, RefundDeposit $refundDeposit)
    {
        $this->bookings = $bookings;
        $this->refundDeposit = $refundDeposit;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('refundDeposit', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        if (! $booking->deposit_is_open) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->refundingBookingId = $booking->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->refundingBookingId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Refund
    // =====================================================================

    public function refund(): void
    {
        if (! $this->loaded || ! $this->refundingBookingId) {
            return;
        }

        $this->authorize('refundDeposit', AmenityBooking::class);

        $booking = $this->refundDeposit->handle(
            $this->bookings->find($this->refundingBookingId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'The ' . $booking->deposit_display . ' deposit was refunded.');
        $this->emit('booking-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-refund-deposit-modal', [
            'booking' => $this->refundingBookingId
                ? $this->bookings->detail($this->refundingBookingId)
                : null,
        ]);
    }
}
