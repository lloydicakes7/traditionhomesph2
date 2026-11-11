<?php

namespace App\Queries\Rbi;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\Sex;
use App\Models\Household\Household;
use App\Models\Resident;

/**
 * What RBI Form B prints for one resident.
 *
 * One inhabitant per sheet: who the person is, where they live, how they earn a
 * living and how far they studied. Everything is the resident's own record; the
 * age, the educational box the attainment is ticked in and the household number
 * are derived, never stored. Only the PhilSys Card Number is read, never the
 * Serial Number.
 */
class FormBQuery
{
    /**
     * The educational boxes the form prints, and the attainment each one holds.
     *
     * @var array<string, array<int, string>>
     */
    const EDUCATION_LEVELS = [
        'elementary' => [EducationalAttainment::ELEMENTARY_LEVEL, EducationalAttainment::ELEMENTARY_GRADUATE],
        'high_school' => [EducationalAttainment::HIGH_SCHOOL_LEVEL, EducationalAttainment::HIGH_SCHOOL_GRADUATE],
        'college' => [EducationalAttainment::COLLEGE_LEVEL, EducationalAttainment::COLLEGE_GRADUATE],
        'post_grad' => [EducationalAttainment::POST_GRADUATE, EducationalAttainment::DOCTORATE],
        'vocational' => [EducationalAttainment::VOCATIONAL],
    ];

    /**
     * The attainments the sheet ticks as Graduate, rather than Under Graduate.
     *
     * @var array<int, string>
     */
    const GRADUATE_LEVELS = [
        EducationalAttainment::ELEMENTARY_GRADUATE,
        EducationalAttainment::HIGH_SCHOOL_GRADUATE,
        EducationalAttainment::COLLEGE_GRADUATE,
        EducationalAttainment::POST_GRADUATE,
        EducationalAttainment::DOCTORATE,
    ];

    // ===== Queries =====

    /**
     * The data Form B prints for a resident.
     *
     * @return array<string, mixed>
     */
    public function sheet(Resident $resident): array
    {
        $resident->loadMissing('user', 'household.property.address');

        $household = $this->household($resident);
        $property = $household ? $household->property : $resident->unit();
        $address = $property ? $property->address : null;
        $education = $this->education($resident);

        return [
            'philsys_no' => (string) $resident->philsys_card_no,
            'last_name' => (string) $resident->surname,
            'suffix' => (string) $resident->suffix,
            'first_name' => (string) $resident->first_name,
            'middle_name' => (string) $resident->middle_name,
            'birth_date' => $resident->birth_date ? $resident->birth_date->format('m/d/Y') : '',
            'birth_place' => (string) $resident->birth_place,
            'sex' => Sex::tryFrom((string) $resident->sex)?->label() ?? '',
            'civil_status' => CivilStatus::tryFrom((string) $resident->civil_status)?->label() ?? '',
            'religion' => (string) $resident->religion,
            'citizenship' => (string) $resident->citizenship,
            'occupation' => (string) $resident->occupation,
            'contact_no' => (string) $resident->mobile_no,
            'email' => $resident->user ? (string) $resident->user->email : '',
            'residence_address' => $this->residenceAddress($property, $address),
            'address' => [
                'region' => $this->addressPart($address, 'region'),
                'province' => $this->addressPart($address, 'province'),
                'city_municipality' => $this->addressPart($address, 'city'),
                'barangay' => $this->addressPart($address, 'barangay'),
                'house_block_lot' => $property ? $property->block_lot_label : '',
                'street_name' => $address ? (string) $address->street : '',
                'subdivision' => $address ? (string) $address->subdivision : '',
                'zip_code' => $address ? (string) $address->zip_code : '',
            ],
            'education' => [
                'level' => $education['level'],
                'graduate' => $education['graduate'],
                'undergraduate' => $education['undergraduate'],
            ],
            'date' => '',
        ];
    }

    // ===== Helpers =====

    /**
     * The household the resident lives in — as a head or as a member.
     */
    private function household(Resident $resident): ?Household
    {
        return $resident->household ?: ($resident->user ? $resident->user->household : null);
    }

    /**
     * The attainment box the record falls in, and whether it is finished.
     *
     * @return array{level: string, graduate: bool, undergraduate: bool}
     */
    private function education(Resident $resident): array
    {
        $attainment = (string) $resident->educational_attainment;
        $level = '';

        foreach (self::EDUCATION_LEVELS as $key => $values) {
            if (in_array($attainment, $values, true)) {
                $level = $key;
            }
        }

        $specified = $level !== '' && $level !== 'vocational';
        $graduate = $specified && in_array($attainment, self::GRADUATE_LEVELS, true);

        return [
            'level' => $level,
            'graduate' => $graduate,
            'undergraduate' => $specified && ! $graduate,
        ];
    }

    /**
     * The residence address as one line: unit, street, subdivision and locality.
     */
    private function residenceAddress($property, $address): string
    {
        return trim(implode(', ', array_filter([
            $property ? $property->block_lot_label : null,
            $address ? $address->street : null,
            $address ? $address->subdivision : null,
            $address && $address->barangay ? 'Barangay ' . $address->barangay : null,
            $address ? $address->city : null,
            $address ? $address->province : null,
            $address ? $address->zip_code : null,
        ])), ', ');
    }

    private function addressPart($address, string $part): string
    {
        $value = $address ? $address->{$part} : null;

        return $value ?: (string) config('thha.locale.' . $part, '');
    }
}
