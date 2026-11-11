<?php

namespace App\Actions\HoaCenter\Members;

use App\Actions\HoaCenter\Members\Concerns\MemberPayload;
use App\Models\Admin;
use App\Models\HoaMember;
use App\Services\ActivityLogService;

class UpdateMember
{
    use MemberPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(
        HoaMember $member,
        array $data,
        $photo,
        bool $removePhoto,
        Admin $by
    ): HoaMember {
        $payload = $this->payload($data);

        if ($photo) {
            $payload['profile_photo_path'] = $this->replacePhoto($photo, $member->profile_photo_path);
        } elseif ($removePhoto) {
            $this->deletePhoto($member->profile_photo_path);
            $payload['profile_photo_path'] = null;
        }

        $member->update($payload);

        $this->activity->log(
            $by,
            'updated',
            'member',
            "Updated HOA member {$member->name}",
            ['hoa_member_id' => $member->id]
        );

        return $member;
    }
}
