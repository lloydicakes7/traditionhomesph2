<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * The landing page of each guard, so a signed-in visitor who asks for a
     * guest-only screen (a login form, say) is sent to the panel they are
     * already inside instead of being bounced to the public site.
     *
     * @var array<string, string>
     */
    private const HOME_BY_GUARD = [
        'web'       => RouteServiceProvider::ADMIN_HOME,
        'homeowner' => RouteServiceProvider::HOMEOWNER_HOME,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $guard = $guard ?? config('auth.defaults.guard');

        if (Auth::guard($guard)->check()) {
            return redirect(self::HOME_BY_GUARD[$guard] ?? RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
