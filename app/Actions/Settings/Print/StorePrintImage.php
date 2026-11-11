<?php

namespace App\Actions\Settings\Print;

use App\Actions\Settings\Print\Concerns\PrintSettingInput;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;
use Illuminate\Http\UploadedFile;

class StorePrintImage
{
    use PrintSettingInput;

    const DIRECTORY = 'documents/print-settings';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $side, UploadedFile $file, Admin $by): string
    {
        $key = $this->imageKey($side);

        $previous = (string) (SettingsHelper::get($key, $this->group()) ?? '');
        $path = StorageHelper::storePrivate($file, self::DIRECTORY);

        StorageHelper::deletePrivate($previous);
        SettingsHelper::set($key, $path, $this->group());

        $this->activity->log($by, 'updated', 'Print Settings', 'Uploaded the ' . $side . ' print image');

        return $path;
    }
}
