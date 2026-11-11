<?php

namespace App\Queries\Settings;

use App\Utility\SettingsHelper;

/**
 * The sign-in policy the office configured, and the answers it implies.
 *
 * Held to by the login controller, the password-expiry middleware and the
 * password rule, so one stored value decides every door the same way.
 */
class SecuritySettingsQuery
{
    const GROUP = 'security';

    // ===== Read =====

    public function load(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            'password_min_length'     => (int) ($settings['password_min_length'] ?? 6),
            'password_require_upper'  => ($settings['password_require_upper'] ?? '0') === '1',
            'password_require_lower'  => ($settings['password_require_lower'] ?? '0') === '1',
            'password_require_number' => ($settings['password_require_number'] ?? '0') === '1',
            'password_require_symbol' => ($settings['password_require_symbol'] ?? '0') === '1',
            'password_expiry_days'    => (int) ($settings['password_expiry_days'] ?? 0),
            'login_max_attempts'      => (int) ($settings['login_max_attempts'] ?? 5),
            'login_lockout_minutes'   => (int) ($settings['login_lockout_minutes'] ?? 15),
            'session_timeout'         => (int) ($settings['session_timeout'] ?? 120),
            'session_expire_on_close' => ($settings['session_expire_on_close'] ?? '0') === '1',
        ];
    }

    public function get(string $key, $default = null)
    {
        return $this->load()[$key] ?? $default;
    }

    // ===== Rules =====

    public function passwordRequirements(): array
    {
        $settings = $this->load();

        return [
            'min_length' => $settings['password_min_length'],
            'upper'      => $settings['password_require_upper'],
            'lower'      => $settings['password_require_lower'],
            'number'     => $settings['password_require_number'],
            'symbol'     => $settings['password_require_symbol'],
        ];
    }

    public function passwordHasExpired($changedAt): bool
    {
        $days = $this->load()['password_expiry_days'];

        if ($days <= 0 || $changedAt === null) {
            return false;
        }

        return $changedAt->copy()->addDays($days)->isPast();
    }

    // ===== Screen =====

    public function form(): array
    {
        return $this->load();
    }
}
