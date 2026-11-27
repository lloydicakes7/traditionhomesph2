<?php

namespace App\Actions\Auth;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;
use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Services\Access\DefaultRoleService;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\Hash;

/**
 * An officer files their own account.
 *
 * The account is filed inactive: an officer registration is never accepted
 * automatically, whatever the resident switch says. An Administrator activates
 * it from User Management, and only then can it sign in.
 */
class RegisterAdmin
{
    private DefaultRoleService $roles;

    public function __construct(DefaultRoleService $roles)
    {
        $this->roles = $roles;
    }

    // ===== Handle =====

    public function handle(array $data): Admin
    {
        if (! $this->registrationOpen()) {
            throw new \RuntimeException('Registration is closed.');
        }

        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $this->roles->role(DefaultRoleSlug::VISITOR)->id,
            'is_active' => false,
        ]);
    }

    // ===== Tools =====

    private function registrationOpen(): bool
    {
        return SettingsHelper::get(
            AuthenticationSettingsQuery::ALLOW_ADMIN_REGISTRATION,
            AuthenticationSettingsQuery::GROUP,
            '0'
        ) === '1';
    }
}
