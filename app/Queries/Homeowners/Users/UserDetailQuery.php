<?php

namespace App\Queries\Homeowners\Users;

use App\Models\User;

class UserDetailQuery
{
    // ===== One account =====

    public function find(int $id): User
    {
        return User::with('resident')->findOrFail($id);
    }

    public function summary(int $id): array
    {
        $user = $this->find($id);

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'is_active'     => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'resident'      => $user->resident,
        ];
    }
}
