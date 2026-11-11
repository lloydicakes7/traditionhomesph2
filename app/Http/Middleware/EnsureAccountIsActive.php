<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    /**
     * Make sure the authenticated account still exists and is active.
     *
     * Usage: account.active:web  or  account.active:homeowner
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $auth = Auth::guard($guard);

        $user = $auth->user();

        if (! $user || ! $user->exists) {
            return $this->deny($request, $guard, 'Your account could not be found.');
        }

        if (! $user->is_active) {
            return $this->deny($request, $guard, 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }

    /**
     * Log the account out of the given guard and send it back to the right login page.
     */
    protected function deny($request, $guard, string $message)
    {
        $auth = Auth::guard($guard);

        if ($auth->check()) {
            $auth->logout();
        }

        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => $message]);
    }
}
