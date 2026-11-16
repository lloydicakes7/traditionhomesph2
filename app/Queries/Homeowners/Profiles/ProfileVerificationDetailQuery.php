<?php

namespace App\Queries\Homeowners\Profiles;

use App\Models\User;

/**
 * One profile as the office reads it: the person, the unit and the household
 * behind the account.
 */
class ProfileVerificationDetailQuery
{
    // ===== Queries =====

    public function find(int $id): ?User
    {
        return User::query()
            ->with([
                'resident',
                'resident.household.property.address',
                'resident.household.headMember.resident',
            ])
            ->find($id);
    }
}
