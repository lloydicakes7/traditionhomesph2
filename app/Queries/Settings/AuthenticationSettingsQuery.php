<?php

namespace App\Queries\Settings;

use App\Utility\SettingsHelper;

/**
 * Whether the public may register.
 *
 * The answers are stored as switches in the `auth` settings group; every door
 * that opens registration asks them here, so one stored value decides the route
 * middleware and the wizard the same way.
 */
class AuthenticationSettingsQuery
{
    const GROUP = 'auth';

    const ALLOW_ADMIN_REGISTRATION = 'allow_admin_registration';

    const ALLOW_RESIDENT_REGISTRATION = 'allow_resident_registration';

    // ===== Read =====

    public function load(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            self::ALLOW_ADMIN_REGISTRATION    => ($settings[self::ALLOW_ADMIN_REGISTRATION] ?? '0') === '1',
            self::ALLOW_RESIDENT_REGISTRATION => ($settings[self::ALLOW_RESIDENT_REGISTRATION] ?? '1') === '1',
        ];
    }

    public function adminRegistrationAllowed(): bool
    {
        return $this->load()[self::ALLOW_ADMIN_REGISTRATION];
    }

    public function residentRegistrationAllowed(): bool
    {
        return $this->load()[self::ALLOW_RESIDENT_REGISTRATION];
    }

    // ===== Screen =====

    public function form(): array
    {
        return $this->load();
    }
}
