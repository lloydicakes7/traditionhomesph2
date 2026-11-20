<?php

namespace App\Queries\Homeowners\Profiles;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Enums\SectorType;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Models\Address;
use App\Models\User;

/**
 * What the onboarding wizard shows: its choices, the account's own record as
 * the form holds it, and the address every unit stands in.
 */
class OnboardingFormQuery
{
    // ===== Options =====

    /**
     * @return array<string, array<string, string>>
     */
    public function options(): array
    {
        return [
            'sexOptions'                   => Sex::labels(),
            'civilStatusOptions'           => CivilStatus::labels(),
            'educationalAttainmentOptions' => EducationalAttainment::labels(),
            'sectorOptions'                => SectorType::labels(),
            'householdTypeOptions'         => HouseholdType::labels(),
            'propertyTypeOptions'          => PropertyType::labels(),
            'suffixOptions'                => Suffix::labels(),
        ];
    }

    // ===== Form =====

    /**
     * The account's resident as the personal step holds it.
     *
     * @return array<string, mixed>
     */
    public function formFor(?User $account): array
    {
        $resident = $account?->resident;

        return [
            'philsys_card_no'        => $resident->philsys_card_no ?? '',
            'surname'                => $resident->surname ?? '',
            'first_name'             => $resident->first_name ?? '',
            'middle_name'            => $resident->middle_name ?? '',
            'suffix'                 => $resident->suffix ?? '',
            'birth_date'             => $resident && $resident->birth_date
                ? $resident->birth_date->format('Y-m-d')
                : '',
            'birth_place'            => $resident->birth_place ?? '',
            'sex'                    => $resident->sex ?? '',
            'civil_status'           => $resident->civil_status ?? '',
            'spouse_name'            => $resident->spouse_name ?? '',
            'citizenship'            => $resident->citizenship ?? '',
            'religion'               => $resident->religion ?? '',
            'occupation'             => $resident->occupation ?? '',
            'mobile_no'              => $resident->mobile_no ?? '',
            'educational_attainment' => $resident->educational_attainment ?? '',
            'sectors'                => $resident ? $resident->sectorValues() : [],
            'solo_parent_special_case' => false,
        ];
    }

    /**
     * The unit the account's household lives in, as the household step holds it.
     *
     * @return array<string, string>
     */
    public function propertyFor(?User $account): array
    {
        $property = $account?->household?->property;

        return [
            'block' => $property ? (string) $property->block : '',
            'lot'   => $property ? (string) $property->lot : '',
            'sqm'   => '',
            'type'  => '',
        ];
    }

    /**
     * The household's own facts, as the household step holds them.
     *
     * @return array<string, string>
     */
    public function householdFor(?User $account): array
    {
        $household = $account?->household;

        return [
            'type'         => $household?->type ?? HouseholdType::OWNER,
            'prev_address' => $household?->prev_address ?? '',
            'relationship' => '',
            'be_head'      => false,
        ];
    }

    /**
     * The address every unit is filed under, shown read-only.
     */
    public function primaryAddress(): ?Address
    {
        return Address::query()->primary()->first();
    }
}
