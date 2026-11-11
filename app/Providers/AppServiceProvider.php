<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Amenity\AmenityBooking;
use App\Models\Request;
use App\Models\User;
use App\Observers\AdminObserver;
use App\Observers\SlipReferenceObserver;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use App\View\AdminLteMenu;
use App\View\Composers\PortalPageComposer;
use App\View\Composers\PublicPageComposer;
use App\View\Composers\HoaInfoComposer;
use App\View\Blades\AdminLteBlades;
use JeroenNoten\LaravelAdminLte\AdminLte;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The panel's AdminLTE service in place of the package's: identical,
        // except that the sidebar it serves never prints a section title with
        // nothing under it (the package binds its own in register(), which
        // runs before this provider).
        $this->app->singleton(AdminLte::class, function () {
            return new AdminLteMenu(config('adminlte.filters', []));
        });

        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade) {
            AdminLteBlades::component($blade);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // The User Manual's copy lives in its own namespace so its many topics
        // split by side and language without colliding with the app's flat lang
        // files: resources/lang/manual/{locale}/{group}.php, read as
        // manual::{side}.{key} and manual::common.{key}. (Registered in boot(),
        // once the translator is bound.)
        Lang::addNamespace('manual', resource_path('lang/manual'));

        Paginator::defaultView('pagination::bootstrap-4');

        // An account's first day: the welcome every officer and homeowner starts
        // with, and — for an officer — the officers who manage users being told
        // an account appeared (see the observers).
        User::observe(UserObserver::class);
        Admin::observe(AdminObserver::class);

        // A request and a booking are filed under a reference the homeowner
        // brings to the office, so both are numbered as they are created.
        Request::observe(SlipReferenceObserver::class);
        AmenityBooking::observe(SlipReferenceObserver::class);

        View::composer('*', HoaInfoComposer::class);
        View::composer('layouts.public', PublicPageComposer::class);
        View::composer('layouts.portal', PortalPageComposer::class);
    }
}

