<?php

namespace App\Actions\HoaCenter\Contents;

use App\Models\Admin;
use App\Models\Content;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class DeleteContent
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Content $content, Admin $by): void
    {
        $title = $content->title;

        StorageHelper::deletePhoto($content->image_path);

        $content->delete();

        $this->activity->log(
            $by,
            'deleted',
            'content',
            "Removed the \"{$title}\" page",
            ['content_id' => $content->id]
        );
    }
}
