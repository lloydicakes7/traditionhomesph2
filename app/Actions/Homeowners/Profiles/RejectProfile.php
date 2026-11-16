<?php

namespace App\Actions\Homeowners\Profiles;

use App\Enums\ProfileStatus;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\ProfileRejectedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Send a submitted profile back to the resident with the one thing to correct.
 *
 * The note is required: an account that is refused has to know what the office
 * found, or it can only submit the same record again.
 */
class RejectProfile
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

    public function handle(User $account, string $note, Admin $by): User
    {
        $note = trim($note);

        if ($note === '') {
            throw new \RuntimeException('Tell the resident what has to be corrected.');
        }

        $account->forceFill([
            'profile_status'               => ProfileStatus::REJECTED,
            'profile_verified_at'          => null,
            'profile_verified_by_admin_id' => null,
            'profile_rejection_note'       => $note,
        ])->save();

        $this->activity->log(
            $by,
            'rejected',
            'homeowner',
            "Sent the profile of {$account->name} ({$account->email}) back for correction",
            ['user_id' => $account->id, 'note' => $note]
        );

        $this->tellTheResident($account, $note);

        return $account;
    }

    // ===== Helpers =====

    private function tellTheResident(User $account, string $note): void
    {
        try {
            $this->notifications->toHomeowner($account, new ProfileRejectedNotification($account, $note));
        } catch (\Throwable $exception) {
            Log::error('Profile rejection notification failed: ' . get_class($exception));
        }
    }
}
