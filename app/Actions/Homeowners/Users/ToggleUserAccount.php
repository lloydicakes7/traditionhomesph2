<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;

/**
 * Enable or disable one homeowner account.
 *
 * The switch only opens or closes the login: accepting a registration is
 * AcceptRegistration's job, and telling the applicant about it is that action's
 * side effect. Nothing is mailed here, so the row answers as fast as any other
 * list toggle.
 */
class ToggleUserAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): bool
    {
        $user = User::findOrFail($id);

        $user->is_active = ! (bool) $user->is_active;
        $user->save();

        $status = $user->is_active ? 'enabled' : 'disabled';

        $this->activity->log(
            $by,
            'toggled',
            'user',
            "{$status} user account {$user->name} ({$user->email})",
            ['user_id' => $user->id, 'is_active' => $user->is_active]
        );

        return $user->is_active;
    }
}
