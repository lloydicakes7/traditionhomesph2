<?php

namespace App\Queries\Homeowners\Properties;

use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Property;

class PropertyStatsQuery
{
    // ===== Summary =====

    public function summary(): array
    {
        return [
            'total'     => Property::count(),
            'withOwner' => Household::distinct('property_id')->count('property_id'),
            'vacant'    => Property::whereDoesntHave('currentHousehold')->count(),
            'addresses' => Address::count(),
        ];
    }
}
