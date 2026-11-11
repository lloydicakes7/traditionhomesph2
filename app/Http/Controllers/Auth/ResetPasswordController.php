<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * Set a new password from a reset link, for either account type.
 *
 * A link for an address with no account ends on the same "invalid token"
 * answer as a tampered one.
 */
class ResetPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest:web', 'guest:homeowner']);
    }

    /**
     * Show the form that accompanies a reset link.
     *
     * @param  string|null  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Write the new password and open the account's session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(ResetPasswordRequest $request, ResetPassword $reset)
    {
        $status = $reset->handle($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect(Auth::guard('web')->check()
            ? RouteServiceProvider::ADMIN_HOME
            : RouteServiceProvider::HOMEOWNER_HOME
        )->with('status', __('passwords.reset'));
    }
}
