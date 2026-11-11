<?php

namespace App\Http\Livewire\Portal\Bookings;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Amenities\AmenityListQuery;

/**
 * Bookings — the reservations the association is holding for this homeowner,
 * and the way to ask for another.
 *
 * The list is scoped to the signed-in account's resident before it is read,
 * which is the ownership rule AmenityBookingPolicy::viewOwn states, applied as
 * the query's boundary: another household's reservations are never in the
 * result set to begin with. The office approves, invoices, takes payment, closes
 * and cancels bookings; the one move a homeowner makes on their own reservation
 * is deleting it, and only while the office has not invoiced it
 * (AmenityBookingPolicy::deleteOwn) — a cancellation is the office's, with a
 * remark the homeowner reads.
 *
 * Whether a request can be filed at all is the office's decision too: a
 * facility is only offered while the office has it open to the portal.
 *
 * Deleting a request is a Bootstrap trigger on `#bookingDeleteModal`
 * (public/js/modal-loader.js): the dialog owns the move — it checks ownership
 * and whether the reservation can still be removed — and the list hears the
 * result through `booking-deleted`.
 */
class BookingList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'booking-requested' => '$refresh',
        'booking-deleted'   => '$refresh',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var AmenityListQuery */
    protected $amenities;

    public function boot(AmenityBookingListQuery $bookings, AmenityListQuery $amenities)
    {
        $this->bookings = $bookings;
        $this->amenities = $amenities;
    }

    /**
     * Render the homeowner's reservations.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.bookings.booking-list', [
            'bookings'  => $this->bookings->forUser($this->account()->id),
            'canBook'   => $this->amenities->bookable()->isNotEmpty(),
        ]);
    }
}
