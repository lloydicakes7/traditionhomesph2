<?php

namespace App\Actions\Collections\Settings;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\HtmlHelper;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\DB;

class UpdateCollectionSettings
{
    const GROUP = 'collections';

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, string $description, Admin $by): void
    {
        DB::transaction(function () use ($data) {
            $persisted = [];

            foreach ($data as $key => $value) {
                if ($key === 'invoice_instructions') {
                    $value = HtmlHelper::sanitize((string) $value);
                }

                $persisted[$key] = is_bool($value) ? (string) (int) $value : (string) $value;
            }

            SettingsHelper::bulkSet($persisted, self::GROUP);
        });

        $this->activity->log($by, 'updated', 'settings', $description);
    }
}
