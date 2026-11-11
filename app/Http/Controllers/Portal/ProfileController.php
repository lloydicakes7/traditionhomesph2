<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Show the signed-in homeowner's profile.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.profile');
    }
}
