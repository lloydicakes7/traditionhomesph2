<?php

namespace App\Queries\Settings;

use App\Utility\SettingsHelper;

/**
 * The association's own details — its name, address and contact — as every
 * page, letterhead and printed document reads them.
 */
class GeneralSettingsQuery
{
    const GROUP = 'hoa_info';

    // ===== Read =====

    public function load(): array
    {
        $settings = SettingsHelper::getByGroup(self::GROUP);

        return [
            'hoa_name'       => $settings['hoa_name'] ?? config('thha.hoa_name', ''),
            'hoa_short_name' => $settings['hoa_short_name'] ?? config('thha.hoa_short_name', ''),
            'logo_path'      => $settings['logo_path'] ?? '',

            'address'        => $settings['address'] ?? config('thha.address', ''),
            'website'        => $settings['website'] ?? config('thha.website', ''),
            'email'          => $settings['email'] ?? config('thha.email', ''),
            'contact_number' => $settings['contact_number'] ?? config('thha.contact_number', ''),
            'working_hours'  => $settings['working_hours'] ?? config('thha.working_hours', ''),
        ];
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->load()[$key] ?? $default;
    }

    // ===== Queries =====

    public function form(): array
    {
        return $this->load();
    }

    public function environment(): array
    {
        return [
            'laravelVersion' => app()->version(),
            'phpVersion'     => PHP_VERSION,
            'webhost'        => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'appName'        => config('app.name', 'THHA'),
            'appEnv'         => config('app.env'),
            'appDebug'       => config('app.debug'),
            'appUrl'         => config('app.url'),
        ];
    }
}
