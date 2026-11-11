<?php

namespace App\Actions\Homeowners\Users;

use App\Enums\RegistrationStatus;
use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\Auth\PasswordSetupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAccount
{
    private ActivityLogService $activity;

    private PasswordSetupService $passwords;

    public function __construct(ActivityLogService $activity, PasswordSetupService $passwords)
    {
        $this->activity = $activity;
        $this->passwords = $passwords;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): User
    {
        $user = User::create([
            'resident_id'         => $data['resident_id'] ?? null,
            'email'               => $data['email'],
            'password'            => Hash::make(Str::random(60)),
            'password_changed_at' => now(),
            'registration_status' => RegistrationStatus::APPROVED,
            'is_active'           => $data['is_active'] ?? true,
        ]);

        $this->passwords->send($user);

        $this->activity->log(
            $by,
            'created',
            'user',
            "Created user account for {$user->name} ({$user->email})",
            ['user_id' => $user->id]
        );

        return $user;
    }
}
