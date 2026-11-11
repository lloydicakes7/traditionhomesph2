<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Actions\Auth\RegisterAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterAdminRequest;

/**
 * Officer registration — the `/admin/register` screen.
 *
 * It is reachable only while the office allows it (`registration.open:admin`
 * on the route), and every account it files waits for an Administrator.
 */
class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest:web', 'guest:homeowner']);
    }

    // ===== Screen =====

    public function showRegistrationForm()
    {
        return view('auth.admin.register');
    }

    // ===== Submit =====

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterAdminRequest $request, RegisterAdmin $register)
    {
        $register->handle($request->validated());

        return redirect()->route('registration.pending');
    }
}
