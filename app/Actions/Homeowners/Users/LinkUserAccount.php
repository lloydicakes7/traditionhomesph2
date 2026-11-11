<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\Resident;
use App\Models\User;
use App\Services\ActivityLogService;

class LinkUserAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $userId, int $residentId, Admin $by): bool
    {
        $user = User::findOrFail($userId);
        $resident = Resident::findOrFail($residentId);

        if ($user->resident_id !== null
            || User::where('resident_id', $resident->id)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->update(['resident_id' => $resident->id]);

        $this->activity->log(
            $by,
            'linked',
            'user',
            "Linked resident {$resident->full_name} to user account {$user->email}",
            ['resident_id' => $resident->id, 'user_id' => $user->id]
        );

        return true;
    }
}
