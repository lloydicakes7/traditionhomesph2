<?php

namespace App\Actions\HoaCenter\Members;

use App\Actions\HoaCenter\Members\Concerns\MemberPayload;
use App\Models\Admin;
use App\Models\HoaMember;
use App\Services\ActivityLogService;

class DeleteMember
{
    use MemberPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(HoaMember $member, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'member',
            "Deleted HOA member {$member->name}",
            ['hoa_member_id' => $member->id]
        );

        $this->deletePhoto($member->profile_photo_path);

        $member->delete();
    }
}
