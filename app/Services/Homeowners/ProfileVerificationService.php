<?php

namespace App\Services\Homeowners;

use App\Enums\ProfileStatus;
use App\Models\User;

/**
 * The rule that a checked profile is re-checked when the resident changes it.
 *
 * The office accepted a person, a unit and a household. Once any of those is
 * edited from the portal, what the office accepted is no longer what is on
 * record, so the account goes back in the queue until it is looked at again.
 * A write by the office itself never calls this.
 */
class ProfileVerificationService
{
    // ===== Operations =====

    /**
     * Put a verified account back in front of the office.
     */
    public function requireReview(?User $account): void
    {
        if (! $account || ! $account->profile_status?->isVerified()) {
            return;
        }

        $account->forceFill([
            'profile_status'               => ProfileStatus::SUBMITTED,
            'profile_submitted_at'         => now(),
            'profile_verified_at'          => null,
            'profile_verified_by_admin_id' => null,
            'profile_rejection_note'       => null,
        ])->save();
    }
}
