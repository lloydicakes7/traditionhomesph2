<?php

namespace App\Actions\Settings\General;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class UpdateHoaInformation
{
    const GROUP = 'hoa_info';

    const LOGO_DIRECTORY = 'settings/logos';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $logo, ?string $currentLogoPath, Admin $by): ?string
    {
        $path = $currentLogoPath;

        DB::transaction(function () use ($data, $logo, $currentLogoPath, &$path) {
            $persisted = array_map(function ($value) {
                return $value ?? '';
            }, $data);

            if ($logo) {
                if ($currentLogoPath) {
                    StorageHelper::deleteFile($currentLogoPath);
                }

                $path = StorageHelper::storeFile($logo, self::LOGO_DIRECTORY);
                $persisted['logo_path'] = $path;
            }

            SettingsHelper::bulkSet($persisted, self::GROUP);
        });

        $this->activity->log(
            $by,
            'updated',
            'settings',
            'Updated HOA information' . ($logo ? ' and uploaded new logo' : ''),
            ['hoa_name' => $data['hoa_name'] ?? null]
        );

        return $path;
    }
}
