<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class HouseholdPropertyController extends Controller
{
    /**
     * Show the household the signed-in homeowner belongs to and its unit.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.household-property');
    }
}
