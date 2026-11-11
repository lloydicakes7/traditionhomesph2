<?php

namespace App\Http\Middleware;

use App\Queries\Settings\SecuritySettingsQuery;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Hold an account to the password-expiry window the office configured.
 *
 * An expired password does not sign the account out; it parks the account on
 * the change-password screen until a new password is set. The check is shared
 * by both guards, because the security settings apply to the admin panel and
 * the homeowner portal alike.
 */
class EnforcePasswordExpiry
{
    /** @var SecuritySettingsQuery */
    protected $securitySettings;

    public function __construct(SecuritySettingsQuery $securitySettings)
    {
        $this->securitySettings = $securitySettings;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard  The guard to check (`web` or `homeowner`).
     * @param  string  $passwordRoute  The route name of that panel's profile page.
     * @return mixed
     */
    public function handle($request, Closure $next, ?string $guard = null, string $passwordRoute = '')
    {
        // Passwords never expire, or nobody is signed in on this guard.
        if ($this->securitySettings->get('password_expiry_days') <= 0) {
            return $next($request);
        }

        $account = Auth::guard($guard)->user();

        if ($account === null || ! $this->securitySettings->passwordHasExpired($account->password_changed_at)) {
            return $next($request);
        }

        // Let the account reach the screen that fixes it, and the way out of
        // the session — being parked here must not trap a signed-in account.
        if ($request->routeIs($passwordRoute) || $request->routeIs('logout') || $request->routeIs('*.logout')) {
            return $next($request);
        }

        return redirect()->route($passwordRoute)->with('status',
            'Your password has expired. Please set a new one to continue.'
        );
    }
}