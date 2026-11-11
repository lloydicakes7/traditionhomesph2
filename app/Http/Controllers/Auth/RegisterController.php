<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

/**
 * The public registration screen — the wizard itself is a Livewire component,
 * because every step validates and stores against the server.
 */
class RegisterController extends Controller
{
    /**
     * Show the five-step registration wizard.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.user.register');
    }
}
