<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\User;

class HomeownerController extends Controller
{
    /**
     * Display the homeowners management page.
     */
    public function homeowners()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.homeowners.homeowners');
    }

    /**
     * Show the accounts waiting for the office to accept their registration.
     */
    public function pendingRegistrations()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.homeowners.pending-registrations');
    }

    /**
     * Show the properties management page.
     */
    public function properties()
    {
        $this->authorize('viewAny', Property::class);

        return view('admin.homeowners.properties');
    }

    /**
     * Show the address management page.
     */
    public function addresses()
    {
        $this->authorize('viewAny', Address::class);

        return view('admin.homeowners.addresses');
    }

    /**
     * Show the household management page.
     */
    public function households()
    {
        $this->authorize('viewAny', Household::class);

        return view('admin.homeowners.households');
    }

    /**
     * Show the residents management page.
     */
    public function residents()
    {
        return view('admin.homeowners.residents');
    }
}
