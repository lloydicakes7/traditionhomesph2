<?php

namespace App\Actions\HoaCenter\Members;

use App\Actions\HoaCenter\Members\Concerns\MemberPayload;
use App\Models\Admin;
use App\Models\HoaMember;
use App\Services\ActivityLogService;

class AddMember
{
    use MemberPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $photo, Admin $by): HoaMember
    {
        $payload = $this->payload($data);

        if ($photo) {
            $payload['profile_photo_path'] = $this->storePhoto($photo);
        }

        $member = HoaMember::create($payload);

        $this->activity->log(
            $by,
            'created',
            'member',
            "Added HOA member {$member->name} ({$member->position})",
            ['hoa_member_id' => $member->id]
        );

        return $member;
    }
}
