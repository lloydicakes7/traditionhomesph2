<?php

namespace App\Actions\HoaCenter\Contents;

use App\Models\Admin;
use App\Models\Content;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;

class CreateContent
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $image, Admin $by): Content
    {
        $payload = Content::payloadFrom($data);
        $payload['slug'] = Content::uniqueSlug($data['title']);

        if ($image) {
            $payload['image_path'] = StorageHelper::storePhoto($image, 'photos/contents');
        }

        $content = Content::create($payload);

        $this->activity->log(
            $by,
            'created',
            'content',
            "Created the \"{$content->title}\" page",
            ['content_id' => $content->id]
        );

        return $content;
    }
}
