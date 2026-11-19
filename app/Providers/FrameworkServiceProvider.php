<?php

namespace App\Providers;

use App\Queries\Settings\AuthenticationSettingsQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class FrameworkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->syncRegistrationLinks();
    }

    protected function syncRegistrationLinks(): void
    {
        try {
            config([
                'adminlte.register_url' => app(AuthenticationSettingsQuery::class)->residentRegistrationAllowed()
                    ? 'portal/register'
                    : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync the registration link; using defaults.', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
