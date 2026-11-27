<?php

namespace App\Http\Middleware;

use App\Services\Access\DefaultRoleService;
use Closure;

/**
 * Make sure the roles the software runs on exist before an admin request is handled.
 */
class EnsureDefaultRolesExist
{
    public function handle($request, Closure $next)
    {
        app(DefaultRoleService::class)->ensure();

        return $next($request);
    }
}
