<?php

namespace App\Queries\Rbi;

use App\Enums\CivilStatus;
use App\Enums\SectorType;
use App\Enums\Sex;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;

/**
 * What RBI Form A prints for one household.
 *
 * The sheet is the household's own page: its address, how many members live in
 * it, and one row per member in the order the form's own column headings ask —
 * name, place and date of birth, age, sex, civil status, citizenship, occupation
 * and the sectoral indicators. Nothing is stored for the form: every figure is
 * read from the household, its property and its members when it is generated.
 */
class FormAQuery
{
    /** How many member rows the sheet prints, filled or blank. */
    const MEMBER_ROWS = 12;

    /**
     * The indicator column's sectors, in the order the form lists them.
     *
     * @var array<int, string>
     */
    const INDICATORS = [
        SectorType::LABOR_FORCE_EMPLOYED,
        SectorType::UNEMPLOYED,
        SectorType::PWD,
        SectorType::OFW,
        SectorType::SOLO_PARENT,
        SectorType::OSY,
        SectorType::OSC,
        SectorType::INDIGENOUS_PEOPLE,
    ];

    // ===== Queries =====

    /**
     * The data Form A prints for a household.
     *
     * @return array<string, mixed>
     */
    public function sheet(Household $household): array
    {
        $household->loadMissing('property.address', 'headMember.resident', 'memberRecords.resident');

        $property = $household->property;
        $members = $this->members($household);

        return [
            'region' => $this->addressPart($property, 'region'),
            'province' => $this->addressPart($property, 'province'),
            'city_municipality' => $this->addressPart($property, 'city'),
            'barangay' => $this->addressPart($property, 'barangay'),
            'household_address' => $this->householdAddress($property),
            'no_of_household_members' => (string) count($members),
            'members' => $this->rows($members),
        ];
    }

    // ===== Helpers =====

    /**
     * The head followed by every member record, once each.
     *
     * @return array<int, Resident>
     */
    private function members(Household $household): array
    {
        $members = [];
        $seen = [];

        $candidates = array_merge(
            [$household->head_resident],
            $household->memberRecords->pluck('resident')->all()
        );

        foreach ($candidates as $resident) {
            if (! $resident instanceof Resident || isset($seen[$resident->id])) {
                continue;
            }

            $seen[$resident->id] = true;
            $members[] = $resident;
        }

        return $members;
    }

    /**
     * One printed row per member, padded with blank rows to the form's height.
     *
     * @param  array<int, Resident>  $members
     * @return array<int, array<string, string>>
     */
    private function rows(array $members): array
    {
        $rows = [];

        foreach ($members as $member) {
            $rows[] = [
                'last_name' => (string) $member->surname,
                'first_name' => (string) $member->first_name,
                'middle_name' => (string) $member->middle_name,
                'extension_name' => (string) $member->suffix,
                'birth_place' => (string) $member->birth_place,
                'birth_date' => $member->birth_date ? $member->birth_date->format('m/d/Y') : '',
                'age' => $member->age !== null ? (string) $member->age : '',
                'sex' => Sex::tryFrom((string) $member->sex)?->label() ?? '',
                'civil_status' => CivilStatus::tryFrom((string) $member->civil_status)?->label() ?? '',
                'citizenship' => (string) $member->citizenship,
                'occupation' => (string) $member->occupation,
                'indicators' => $this->indicators($member),
            ];
        }

        while (count($rows) < self::MEMBER_ROWS) {
            $rows[] = array_fill_keys([
                'last_name', 'first_name', 'middle_name', 'extension_name', 'birth_place',
                'birth_date', 'age', 'sex', 'civil_status', 'citizenship', 'occupation', 'indicators',
            ], '');
        }

        return $rows;
    }

    /**
     * A member's sectoral indicators, as the form's own column writes them.
     */
    private function indicators(Resident $member): string
    {
        $tags = $member->sectorValues();
        $labels = [];

        foreach (self::INDICATORS as $sector) {
            if (in_array($sector, $tags, true)) {
                $labels[] = SectorType::indicatorLabel($sector);
            }
        }

        return implode(', ', $labels);
    }

    private function addressPart(?Property $property, string $part): string
    {
        $address = $property ? $property->address : null;
        $value = $address ? $address->{$part} : null;

        return $value ?: (string) config('thha.locale.' . $part, '');
    }

    private function householdAddress(?Property $property): string
    {
        if (! $property) {
            return '';
        }

        $address = $property->address;

        return trim(implode(', ', array_filter([
            $property->block_lot_label,
            $address ? $address->street : null,
            $address ? $address->subdivision : null,
        ])), ', ');
    }
}
