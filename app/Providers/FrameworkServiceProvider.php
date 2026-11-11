<?php

namespace App\Providers;

use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Utility\SettingsHelper;
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
        $this->syncSecuritySettings();
        $this->syncRegistrationLinks();
    }

    protected function syncSecuritySettings(): void
    {
        try {
            $settings = SettingsHelper::getByGroup('security');

            if (! empty($settings['session_timeout'])) {
                config(['session.lifetime' => (int) $settings['session_timeout']]);
            }

            if (isset($settings['session_expire_on_close'])) {
                config(['session.expire_on_close' => $settings['session_expire_on_close'] === '1']);
            }

            if (! empty($settings['login_lockout_minutes'])) {
                $lockoutSeconds = (int) $settings['login_lockout_minutes'] * 60;
                config(['auth.passwords.admins.throttle' => $lockoutSeconds]);
                config(['auth.passwords.users.throttle' => $lockoutSeconds]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync security settings to framework config; using defaults.', [
                'exception' => $e->getMessage(),
            ]);
        }
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
