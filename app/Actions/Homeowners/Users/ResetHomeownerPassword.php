<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\Auth\PasswordSetupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetHomeownerPassword
{
    private ActivityLogService $activity;

    private PasswordSetupService $passwords;

    public function __construct(ActivityLogService $activity, PasswordSetupService $passwords)
    {
        $this->activity = $activity;
        $this->passwords = $passwords;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): User
    {
        $user = User::findOrFail($id);

        $user->forceFill([
            'password'            => Hash::make(Str::random(60)),
            'password_changed_at' => now(),
        ])->save();

        $this->passwords->send($user);

        $this->activity->log(
            $by,
            'reset_password',
            'user',
            "Reset the password for {$user->name} ({$user->email})",
            ['user_id' => $user->id]
        );

        return $user;
    }
}
