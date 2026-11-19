<?php

namespace App\Queries\Homeowners\Households;

use App\Models\Address;
use App\Models\Property;

/**
 * What the office already holds for a unit named by its block and lot.
 *
 * A walk-in resident is placed by the address they give at the desk. This reads
 * that address back from the office's own records — the unit, the household
 * living in it and who heads it — so linking to an existing household or
 * creating a new one is decided from what is on file rather than from what the
 * officer remembers.
 */
class HouseholdUnitQuery
{
    /**
     * The unit at this block and lot, with the household that holds it.
     *
     * The resident is named only to tell whether they already head that
     * household, which the form says instead of offering to place them in it.
     *
     * @return array<string, mixed>|null
     */
    public function atUnit(int $block, int $lot, ?int $residentId = null): ?array
    {
        $property = Property::query()
            ->atUnit($block, $lot)
            ->with([
                'address',
                'currentHousehold.headMember.resident',
            ])
            ->first();

        if ($property === null) {
            return null;
        }

        $household = $property->currentHousehold;
        $head = $household ? $household->head_resident : null;

        return [
            'property_id'  => $property->id,
            'address'      => $property->full_address_label,
            'is_occupied'  => $household !== null,
            'household_id' => $household ? $household->id : null,
            'is_head'      => $head !== null && $residentId !== null && (int) $head->id === $residentId,
        ];
    }

    /**
     * The association's own address — the one every unit stands in.
     */
    public function associationAddress(): ?Address
    {
        return Address::query()->primary()->first();
    }
}
