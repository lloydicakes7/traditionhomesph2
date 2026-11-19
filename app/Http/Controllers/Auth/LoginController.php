<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SignInAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use ThrottlesLogins;

    const MAX_ATTEMPTS = 10;

    const DECAY_MINUTES = 3;

    // ===== Screen =====

    /**
     * Show the unified sign-in form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        $home = $this->signedInHome();

        if ($home !== null) {
            return redirect($home);
        }

        return view('auth.login');
    }

    // ===== Sign in =====

    /**
     * Sign in whichever account holds the email address.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request, SignInAccount $signIn)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $outcome = $signIn->handle(
            (string) $request->input('email'),
            (string) $request->input('password'),
            (bool) $request->filled('remember'),
            (string) $request->ip()
        );

        if ($outcome === SignInAccount::FAILED) {
            $this->incrementLoginAttempts($request);

            return $this->failedLogin($request, __('auth.failed'));
        }

        if ($outcome === SignInAccount::INACTIVE) {
            return $this->failedLogin($request, __('auth.inactive'));
        }

        if ($outcome === SignInAccount::UNVERIFIED) {
            return redirect()
                ->route('verification.resend')
                ->with('verification_email', (string) $request->input('email'));
        }

        $this->clearLoginAttempts($request);

        if ($outcome === SignInAccount::HOMEOWNER && ! Auth::guard('homeowner')->user()->hasCompletedProfile()) {
            return redirect()->route('portal.onboarding');
        }

        return redirect()->intended(
            $outcome === SignInAccount::HOMEOWNER
                ? RouteServiceProvider::HOMEOWNER_HOME
                : RouteServiceProvider::ADMIN_HOME
        );
    }

    // ===== Throttle =====

    /**
     * The form field the throttle counts attempts by.
     */
    public function username()
    {
        return 'email';
    }

    /**
     * The number of failed attempts a device may make in one window.
     */
    public function maxAttempts()
    {
        return self::MAX_ATTEMPTS;
    }

    /**
     * How long, in minutes, a blocked device waits.
     */
    public function decayMinutes()
    {
        return self::DECAY_MINUTES;
    }

    /**
     * The key the throttle counts attempts by, one key per device.
     *
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return (string) $request->ip();
    }

    // ===== Tools =====

    /**
     * The panel the account already signed in on this browser belongs to.
     */
    private function signedInHome(): ?string
    {
        if (Auth::guard('web')->check()) {
            return RouteServiceProvider::ADMIN_HOME;
        }

        if (Auth::guard('homeowner')->check()) {
            return RouteServiceProvider::HOMEOWNER_HOME;
        }

        return null;
    }

    /**
     * Refuse a sign-in without saying what was wrong with it.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function failedLogin(Request $request, string $message)
    {
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => $message]);
    }
}
