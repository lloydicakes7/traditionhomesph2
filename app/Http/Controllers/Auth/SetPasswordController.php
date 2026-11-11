<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SetPasswordRequest;
use Illuminate\Http\Request;

/**
 * The screen an office-issued set-password link lands on.
 *
 * The account chooses its own password here, is not signed in, and is sent to
 * the sign-in screen once the password has been saved.
 */
class SetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest:web', 'guest:homeowner']);
        $this->middleware('throttle:6,1')->only('update');
    }

    // ===== Screen =====

    public function show(Request $request)
    {
        return view('auth.set-password', [
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email'),
        ]);
    }

    // ===== Save =====

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SetPasswordRequest $request, SetPassword $setPassword)
    {
        if (! $setPassword->handle($request->validated())) {
            return back()->withErrors(['email' => __('passwords.token')]);
        }

        return redirect()->route('login')->with('status', __('auth.set_password_saved'));
    }
}
