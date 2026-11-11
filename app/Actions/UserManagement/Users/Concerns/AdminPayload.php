<?php

namespace App\Actions\UserManagement\Users\Concerns;

trait AdminPayload
{
    // ===== Payload =====

    /**
     * The columns a form decides. The password is not one of them: the office
     * never sets or reads it.
     */
    protected function payload(array $data): array
    {
        return [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role_id'   => $data['role_id'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }
}
