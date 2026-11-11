<?php

namespace App\Actions\HoaCenter\Contents;

use App\Models\Admin;
use App\Models\Content;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class UpdateContent
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(
        Content $content,
        array $data,
        $image,
        bool $removeImage,
        Admin $by
    ): Content {
        $payload = Content::payloadFrom($data);

        if ($image) {
            $payload['image_path'] = StorageHelper::replacePhoto($image, $content->image_path, 'photos/contents');
        } elseif ($removeImage) {
            StorageHelper::deletePhoto($content->image_path);
            $payload['image_path'] = null;
        }

        $content->update($payload);

        $this->activity->log(
            $by,
            'updated',
            'content',
            "Updated the \"{$content->title}\" page",
            ['content_id' => $content->id]
        );

        return $content;
    }
}
