<?php

namespace App\Queries\Homeowners\Households;

use App\Models\Household\HouseholdLinkRequest;
use Illuminate\Database\Eloquent\Collection;

class HouseholdLinkRequestListQuery
{
    public function pendingFor(int $householdId): Collection
    {
        return HouseholdLinkRequest::query()
            ->pending()
            ->where('household_id', $householdId)
            ->with('resident:id,surname,first_name,middle_name,suffix')
            ->orderBy('created_at')
            ->get();
    }
}
