<?php

namespace App\Http\Middleware;

use App\Queries\Settings\AuthenticationSettingsQuery;
use Closure;

/**
 * Close a registration screen the office has switched off.
 *
 * A GET shows the "registration closed" notice; anything else answers 404.
 * The screen is never merely hidden: the route itself is closed.
 */
class EnsureRegistrationIsOpen
{
    public function handle($request, Closure $next, string $audience = 'resident')
    {
        $settings = app(AuthenticationSettingsQuery::class);

        $open = $audience === 'admin'
            ? $settings->adminRegistrationAllowed()
            : $settings->residentRegistrationAllowed();

        if ($open) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return response()->view('auth.registration-closed', [], 403);
        }

        abort(404);
    }
}
