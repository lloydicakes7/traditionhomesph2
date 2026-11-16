<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Keep a resident whose profile the office has not checked on the one form.
 *
 * The account can sign in and give its own details; every service the portal
 * offers stays closed until the office has accepted the person, the unit and the
 * household behind the account.
 */
class EnsureProfileIsVerified
{
    /**
     * Send the account to the onboarding form until its profile is verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $account = Auth::guard('homeowner')->user();

        if ($account === null || $account->hasCompletedProfile()) {
            return $next($request);
        }

        return redirect()->route('portal.onboarding')->with('notify', [
            'type'    => 'warning',
            'message' => __('auth.onboarding.gate_notice'),
        ]);
    }
}
