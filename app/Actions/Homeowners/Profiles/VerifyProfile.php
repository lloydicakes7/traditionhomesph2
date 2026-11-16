<?php

namespace App\Actions\Homeowners\Profiles;

use App\Enums\ProfileStatus;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\ProfileVerifiedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Accept a submitted profile, which opens the portal's services to the account.
 *
 * The office is accepting the person, the unit and the household together: the
 * address is checked here, so the account is released only once somebody has
 * looked at it.
 */
class VerifyProfile
{
    /** @var ActivityLogService */
    private $activity;

    /** @var NotificationService */
    private $notifications;

    public function __construct(ActivityLogService $activity, NotificationService $notifications)
    {
        $this->activity = $activity;
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(User $account, Admin $by): User
    {
        $account->forceFill([
            'profile_status'               => ProfileStatus::VERIFIED,
            'profile_verified_at'          => now(),
            'profile_verified_by_admin_id' => $by->id,
            'profile_rejection_note'       => null,
        ])->save();

        $this->activity->log(
            $by,
            'verified',
            'homeowner',
            "Verified the profile of {$account->name} ({$account->email})",
            ['user_id' => $account->id]
        );

        $this->tellTheResident($account);

        return $account;
    }

    // ===== Helpers =====

    private function tellTheResident(User $account): void
    {
        try {
            $this->notifications->toHomeowner($account, new ProfileVerifiedNotification($account));
        } catch (\Throwable $exception) {
            Log::error('Profile verification notification failed: ' . get_class($exception));
        }
    }
}
