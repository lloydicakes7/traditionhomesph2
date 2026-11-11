<?php

namespace App\Queries\Settings;

use App\Utility\SettingsHelper;

/**
 * Whether the public may register, and what happens to the account when they do.
 *
 * The three answers are stored as switches in the `auth` settings group; every
 * door that opens registration asks them here, so one stored value decides the
 * route middleware, the wizard and the write the same way.
 */
class AuthenticationSettingsQuery
{
    const GROUP = 'auth';

    const ALLOW_ADMIN_REGISTRATION = 'allow_admin_registration';

    const ALLOW_RESIDENT_REGISTRATION = 'allow_resident_registration';

    const AUTO_ACCEPT_REGISTRATION = 'auto_accept_registration';

    // ===== Read =====

    public function load(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            self::ALLOW_ADMIN_REGISTRATION    => ($settings[self::ALLOW_ADMIN_REGISTRATION] ?? '0') === '1',
            self::ALLOW_RESIDENT_REGISTRATION => ($settings[self::ALLOW_RESIDENT_REGISTRATION] ?? '1') === '1',
            self::AUTO_ACCEPT_REGISTRATION    => ($settings[self::AUTO_ACCEPT_REGISTRATION] ?? '0') === '1',
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

    public function autoAccept(): bool
    {
        return $this->load()[self::AUTO_ACCEPT_REGISTRATION];
    }

    // ===== Screen =====

    public function form(): array
    {
        return $this->load();
    }
}
