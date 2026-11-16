<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * The one way an email address is confirmed.
 *
 * The link carries a signature and the account's own hash, so it can be opened
 * in any browser: the account is marked verified and the person is sent to the
 * sign-in page, never signed in by the click alone.
 */
class VerifyEmailController extends Controller
{
    /**
     * Confirm the account's email address and send it to the login page.
     *
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals(sha1($user->getEmailForVerification()), (string) $hash), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()
            ->route('login')
            ->with('status', __('auth.verified_sign_in'));
    }
}
