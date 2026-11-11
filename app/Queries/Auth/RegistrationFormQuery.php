<?php

namespace App\Queries\Auth;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Enums\SectorType;
use App\Enums\Sex;
use App\Models\Address;
use App\Models\Property;

/**
 * The data the unified registration wizard shows: its choices, the address the
 * association assigns, the unit the applicant names, and what may be said about
 * it.
 */
class RegistrationFormQuery
{
    // ===== Options =====

    /**
     * The fixed choices the wizard's selects offer.
     *
     * @return array<string, array<string, string>>
     */
    public function options(): array
    {
        return [
            'sexOptions'                   => Sex::labels(),
            'civilStatusOptions'           => CivilStatus::labels(),
            'educationalAttainmentOptions' => EducationalAttainment::labels(),
            'propertyTypeOptions'          => PropertyType::labels(),
            'householdTypeOptions'         => HouseholdType::labels(),
            'sectorOptions'                => SectorType::labels(),
        ];
    }

    /**
     * A blank personal form, in the resident form's own field names.
     *
     * @return array<string, mixed>
     */
    public function blankForm(): array
    {
        return [
            'surname'                => '',
            'first_name'             => '',
            'middle_name'            => '',
            'suffix'                 => '',
            'birth_date'             => '',
            'birth_place'            => '',
            'mobile_no'              => '',
            'sex'                    => '',
            'civil_status'           => '',
            'spouse_name'            => '',
            'religion'               => '',
            'citizenship'            => 'Filipino',
            'occupation'             => '',
            'educational_attainment' => '',
            'sectors'                => [],
        ];
    }

    // ===== Address =====

    /**
     * The address the association assigns to every unit, shown read-only.
     */
    public function primaryAddress(): ?Address
    {
        return Address::query()->primary()->first();
    }

    /**
     * The unit the applicant named, or null while the office has no record of
     * that block and lot.
     */
    public function propertyAt(int $block, int $lot): ?Property
    {
        return Property::query()
            ->atUnit($block, $lot)
            ->with(['address', 'currentHousehold.headMember.resident', 'currentHousehold.memberRecords.resident'])
            ->first();
    }

    /**
     * What the wizard may say about a unit: its address, whether a household
     * already lives in it, and the head's initials only.
     *
     * The applicant has to recognise the household they claim to live in, so
     * the surname and the initial of the first name are shown; a birth date, a
     * contact number or any other personal detail of that resident is not.
     *
     * @return array<string, mixed>
     */
    public function householdAt(Property $property): array
    {
        $household = $property->currentHousehold;
        $head = $household?->head_resident;

        return [
            'property_id'   => $property->id,
            'address'       => $property->full_address_label,
            'is_occupied'   => $household !== null,
            'is_new'        => false,
            'head_initials' => $head ? $this->initials($head->first_name, $head->surname) : null,
        ];
    }

    /**
     * "J. Dela Cruz" — the most the matching screen may show about the head.
     */
    private function initials(?string $first_name, ?string $surname): string
    {
        $initial = $first_name !== null && $first_name !== ''
            ? mb_strtoupper(mb_substr($first_name, 0, 1)) . '. '
            : '';

        return trim($initial . (string) $surname);
    }
}
