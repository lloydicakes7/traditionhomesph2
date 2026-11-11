<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/**
 * The portal's requests page.
 *
 * The page lists the homeowner's own requests (see the Livewire list). The
 * issued document is never downloaded here: it is printed by the office and
 * handed over at the counter. What the homeowner prints instead is the
 * transaction slip (see Support\RequestSlipController).
 */
class RequestController extends Controller
{
    /**
     * Show the requests page — what this homeowner has asked the office for.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.requests');
    }
}
