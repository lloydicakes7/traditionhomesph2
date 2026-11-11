<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SignInAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Queries\Settings\SecuritySettingsQuery;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use ThrottlesLogins;

    /** @var SecuritySettingsQuery */
    protected $securitySettings;

    public function __construct(SecuritySettingsQuery $securitySettings)
    {
        $this->securitySettings = $securitySettings;
    }

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

        $this->clearLoginAttempts($request);

        if ($outcome === SignInAccount::PENDING) {
            return redirect()->route('registration.pending');
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
     * The number of failed attempts before the lockout window opens.
     */
    public function maxAttempts()
    {
        return (int) $this->securitySettings->get('login_max_attempts', 5);
    }

    /**
     * How long, in minutes, a locked-out visitor waits.
     */
    public function decayMinutes()
    {
        return (int) $this->securitySettings->get('login_lockout_minutes', 15);
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
