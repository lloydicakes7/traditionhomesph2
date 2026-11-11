<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;

/**
 * "Email me a reset link" for both account types.
 *
 * The visitor is told the same thing whether or not the address has an account,
 * so the screen cannot be used to discover one — or which kind it is.
 */
class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest:web', 'guest:homeowner']);
        $this->middleware('throttle:6,1')->only('sendResetLinkEmail');
    }

    /**
     * Show the request form.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send the reset link, if the address has an account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request, SendPasswordResetLink $send)
    {
        $send->handle((string) $request->validated()['email']);

        return back()->with('status', __('passwords.sent'));
    }
}
