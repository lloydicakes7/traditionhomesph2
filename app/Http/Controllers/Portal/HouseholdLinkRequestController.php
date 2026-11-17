<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class HouseholdLinkRequestController extends Controller
{
    public function index()
    {
        return view('portal.household-requests');
    }
}
