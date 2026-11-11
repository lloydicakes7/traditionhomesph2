<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\Cache;

class HoaInfoComposer
{

    const EXCLUDED_VIEW_PREFIX = 'pdf.';

    public function compose(View $view): void
    {
        if (strpos((string) $view->name(), self::EXCLUDED_VIEW_PREFIX) === 0) {
            return;
        }

        $settings = SettingsHelper::getByGroup('hoa_info');

        $view->with([
            'hoa_name'        => $settings['hoa_name'] ?? config('thha.hoa_name', 'Tradition Homes Phase 2 Homeowners Association, Inc.'),
            'hoa_short_name'  => $settings['hoa_short_name'] ?? config('thha.hoa_short_name', 'Tradition Homes'),
            'hoa_description' => config('thha.hoa_description'),
            'logo_path'       => $settings['logo_path'] ?? config('thha.logo_path', ' /images/logo.jpg'),
            'hoa_address'     => $settings['address'] ?? config('thha.address', '123 Main St, Anytown, Philippines'),
            'website'         => $settings['website'] ?? config('thha.website'),
            'email'           => $settings['email'] ?? config('thha.email'),
            'contact_number'  => $settings['contact_number'] ?? config('thha.contact_number'),
        ]);
    }
}
