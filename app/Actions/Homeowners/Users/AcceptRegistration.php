<?php

namespace App\Actions\Homeowners\Users;

use App\Enums\RegistrationStatus;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\RegistrationApprovedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Accept one self-registered account, so the applicant may sign in.
 *
 * The registration is filed as `pending` (see \App\Models\User); accepting it
 * approves the registration and leaves the account enabled. The notification is
 * sent once the write has committed, and a mail server that is down never
 * unwinds an approval already recorded.
 */
class AcceptRegistration
{
    private ActivityLogService $activity;

    private NotificationService $notifications;

    public function __construct(ActivityLogService $activity, NotificationService $notifications)
    {
        $this->activity = $activity;
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): User
    {
        $user = User::pendingRegistration()->findOrFail($id);

        $user->registration_status = RegistrationStatus::APPROVED;
        $user->is_active = true;
        $user->save();

        $this->tellTheApplicant($user);

        $this->activity->log(
            $by,
            'approved',
            'user',
            "Approved the registration of {$user->name} ({$user->email})",
            ['user_id' => $user->id]
        );

        return $user;
    }

    // ===== Helpers =====

    private function tellTheApplicant(User $user): void
    {
        try {
            $this->notifications->toHomeowner($user, new RegistrationApprovedNotification($user));
        } catch (\Throwable $exception) {
            Log::error('Registration approval notification failed: ' . get_class($exception));
        }
    }
}
