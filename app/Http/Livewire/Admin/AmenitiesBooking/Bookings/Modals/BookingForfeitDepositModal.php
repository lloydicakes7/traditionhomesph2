<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals;

use App\Actions\Amenities\Bookings\ForfeitDeposit;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Amenities\AmenityBookingListQuery;

/**
 * Forfeit Deposit — the confirmation a row's Forfeit Deposit action opens.
 *
 * Opened client-side by Bootstrap (`#bookingForfeitDepositModal`) with the
 * booking's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * The deposit is kept by the association, so the move is confirmed before it is
 * made.
 *
 * Only a reservation whose deposit is still held offers the move
 * (`deposit_is_open`), which the dialog asks again.
 */
class BookingForfeitDepositModal extends AdminModalComponent
{
    const MODAL_ID = 'bookingForfeitDepositModal';

    /** @var int|null Reservation the confirmation applies to */
    public $forfeitingBookingId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadBookingForfeitDepositModal'  => 'load',
        'closeBookingForfeitDepositModal' => 'closeModal',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var ForfeitDeposit */
    protected $forfeitDeposit;

    public function boot(AmenityBookingListQuery $bookings, ForfeitDeposit $forfeitDeposit)
    {
        $this->bookings = $bookings;
        $this->forfeitDeposit = $forfeitDeposit;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('forfeitDeposit', AmenityBooking::class);

        $booking = $this->bookings->detail($id);

        if (! $booking->deposit_is_open) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->forfeitingBookingId = $booking->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->forfeitingBookingId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Forfeit
    // =====================================================================

    public function forfeit(): void
    {
        if (! $this->loaded || ! $this->forfeitingBookingId) {
            return;
        }

        $this->authorize('forfeitDeposit', AmenityBooking::class);

        $booking = $this->forfeitDeposit->handle(
            $this->bookings->find($this->forfeitingBookingId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'The ' . $booking->deposit_display . ' deposit was forfeited.');
        $this->emit('booking-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.amenities-booking.bookings.modals.booking-forfeit-deposit-modal', [
            'booking' => $this->forfeitingBookingId
                ? $this->bookings->detail($this->forfeitingBookingId)
                : null,
        ]);
    }
}
