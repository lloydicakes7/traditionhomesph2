<?php

namespace App\Queries\Settings;

use App\Models\Admin;

class ProfileSettingsQuery
{
    // ===== Queries =====

    public function form(Admin $admin): array
    {
        return [
            'name'      => $admin->name,
            'email'     => $admin->email,
            'has_photo' => $admin->profile_photo_path !== null,
        ];
    }
}
