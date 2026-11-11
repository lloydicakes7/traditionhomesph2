<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\Resident;
use App\Services\ActivityLogService;

class UnlinkResidentAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $residentId, Admin $by): bool
    {
        $resident = Resident::with('user')->findOrFail($residentId);

        if (! $resident->user) {
            return false;
        }

        $email = $resident->user->email;
        $resident->user->update(['resident_id' => null]);

        $this->activity->log(
            $by,
            'unlinked',
            'user',
            "Unlinked user account ({$email}) from resident {$resident->full_name}",
            ['resident_id' => $resident->id, 'email' => $email]
        );

        return true;
    }
}
