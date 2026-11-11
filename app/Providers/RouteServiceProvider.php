<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The path to the "home" route for your admin.
     *
     * @var string
     */
    public const ADMIN_HOME = '/admin';

    /**
     * The path to the "home" route for the homeowner portal.
     *
     * @var string
     */
    public const HOMEOWNER_HOME = '/portal';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAdminRoutes();

        $this->mapPortalRoutes();
    }

    /**
     * Define the "web" (public) routes for the application.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
             ->middleware('web')
             ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "homeowner" routes for the application.
     *
     * @return void
     */
    protected function mapPortalRoutes()
    {
        Route::prefix('portal')
             ->middleware('web')
             ->group(base_path('routes/portal.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/api.php'));
    }
}
