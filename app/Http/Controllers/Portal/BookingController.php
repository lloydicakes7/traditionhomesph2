<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Show the signed-in homeowner's reservations.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.bookings');
    }
}
