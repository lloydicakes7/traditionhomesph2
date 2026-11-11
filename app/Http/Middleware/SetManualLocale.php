<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Choose the language the User Manual is read in.
 *
 * The manual is its own shell and is the only surface that offers a language
 * selector, so this is scoped to its two routes rather than the whole app. The
 * chosen locale arrives as `?lang=`, is remembered for the session (sidebar
 * links carry no query string), and is applied with `App::setLocale()` so the
 * `manual` translation namespace and the `adminlte` package's own copy both
 * answer in it. An unsupported value falls back to the app's default locale.
 */
class SetManualLocale
{
    /** Where the reader's chosen locale is remembered between pages. */
    public const SESSION_KEY = 'manual.locale';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locales = array_keys(config('manual.locales', []));
        $locale = $request->query('lang', $request->session()->get(self::SESSION_KEY));

        if (! in_array($locale, $locales, true)) {
            $locale = config('app.locale');
        }

        $request->session()->put(self::SESSION_KEY, $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
