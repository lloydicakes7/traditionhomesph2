<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\VerifyEmailAddress;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * The one way an email address is confirmed.
 *
 * The link carries a signature and the account's own hash, so it can be opened
 * in any browser: the account is marked verified and the person is sent to the
 * sign-in page, never signed in by the click alone. A link that was signed for
 * this account but has run out of time opens a page that asks for a new one.
 */
class VerifyEmailController extends Controller
{
    /**
     * Confirm the account's email address and send it to the login page.
     *
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $id, $hash, VerifyEmailAddress $verify)
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals(sha1($user->getEmailForVerification()), (string) $hash), 403);

        abort_unless(URL::hasCorrectSignature($request), 403);

        if ($this->hasExpired($request)) {
            return view('auth.verify-expired', [
                'verificationEmail' => $user->getEmailForVerification(),
            ]);
        }

        $verify->handle($user);

        return redirect()
            ->route('login')
            ->with('status', __('auth.verified_sign_in'));
    }

    private function hasExpired(Request $request): bool
    {
        $expires = $request->query('expires');

        return $expires !== null && now()->getTimestamp() > (int) $expires;
    }
}
