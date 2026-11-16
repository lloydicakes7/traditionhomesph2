<?php

namespace App\Actions\Homeowners\Profiles;

use App\Models\Resident;
use App\Services\Homeowners\ProfileVerificationService;

/**
 * A resident saving their own personal information from the portal.
 *
 * Nothing the office already checked survives the edit: when the account was
 * verified, the change puts it back in the verification queue.
 */
class UpdateOwnProfile
{
    /** @var ProfileVerificationService */
    private $profiles;

    public function __construct(ProfileVerificationService $profiles)
    {
        $this->profiles = $profiles;
    }

    // ===== Handle =====

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Resident $resident, array $data): Resident
    {
        $resident->update(Resident::payloadFrom($data));

        $this->profiles->requireReview($resident->user);

        return $resident;
    }
}
