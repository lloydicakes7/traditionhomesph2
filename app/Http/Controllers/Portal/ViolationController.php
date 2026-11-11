<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ViolationController extends Controller
{
    /**
     * Show the homeowner's violations.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.violations');
    }
}
