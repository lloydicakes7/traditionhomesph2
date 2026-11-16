<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Household\Household;
use App\Services\Homeowners\ProfileVerificationService;

class UpdateHouseholdFacts
{
    /** @var ProfileVerificationService */
    private $profiles;

    public function __construct(ProfileVerificationService $profiles)
    {
        $this->profiles = $profiles;
    }

    // ===== Handle =====

    public function handle(Household $household, array $data): Household
    {
        $household->update([
            'type'         => $data['type'],
            'prev_address' => $this->blankToNull($data['prev_address'] ?? null),
        ]);

        $this->profiles->requireReview($household->user);

        return $household;
    }

    // ===== Helpers =====

    private function blankToNull($value)
    {
        return $value === '' || $value === null ? null : $value;
    }
}
