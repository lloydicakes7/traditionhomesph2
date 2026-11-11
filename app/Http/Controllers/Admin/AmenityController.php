<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;

/**
 * The three pages of the Amenities Booking module — reservations, their
 * calendar, and the facilities themselves.
 *
 * Thin by design: each action authorizes the page and returns its view. The
 * data, the filters and the actions belong to the Livewire components the
 * views embed, and the rules behind them to the module's services.
 */
class AmenityController extends Controller
{
    /**
     * Show the amenities page — the bookable facilities and their rules.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', Amenity::class);

        return view('admin.amenities.index');
    }

    /**
     * Show the bookings page — every reservation on the books.
     *
     * @return \Illuminate\View\View
     */
    public function bookings()
    {
        $this->authorize('viewAny', AmenityBooking::class);

        return view('admin.amenities.bookings');
    }

    /**
     * Show the calendar view — the reservations of one month.
     *
     * @return \Illuminate\View\View
     */
    public function calendar()
    {
        $this->authorize('viewAny', AmenityBooking::class);

        return view('admin.amenities.calendar');
    }
}
