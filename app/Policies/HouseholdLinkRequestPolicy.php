<?php

namespace App\Policies;

use App\Models\Household\HouseholdLinkRequest;
use App\Models\User;

class HouseholdLinkRequestPolicy
{
    public function decideOwn(User $user, HouseholdLinkRequest $link): bool
    {
        return $this->isHead($user, $link);
    }

    private function isHead(User $user, HouseholdLinkRequest $link): bool
    {
        $head = $link->household?->head_resident;

        return $head !== null && (int) $user->resident_id === (int) $head->id;
    }
}
