<?php

namespace App\Queries\Homeowners\Residents;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\SectorType;
use App\Enums\Sex;
use App\Models\Resident;

class ResidentFormQuery
{
    // ===== Options =====

    public function options(): array
    {
        return [
            'sexOptions'                   => Sex::labels(),
            'civilStatusOptions'           => CivilStatus::labels(),
            'educationalAttainmentOptions' => EducationalAttainment::labels(),
            'sectorOptions'                => SectorType::labels(),
        ];
    }

    public function blank(): array
    {
        return array_fill_keys(array_keys($this->options()), []);
    }

    // ===== Form =====

    /**
     * A blank resident form, for a dialog that is creating a resident.
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
            'mobile_no'              => '',
            'sex'                    => '',
            'birth_date'             => '',
            'birth_place'            => '',
            'religion'               => '',
            'citizenship'            => '',
            'civil_status'           => '',
            'spouse_name'            => '',
            'occupation'             => '',
            'educational_attainment' => '',
        ] + $this->profileBlank();
    }

    /**
     * A saved resident as the form holds it.
     *
     * @return array<string, mixed>
     */
    public function form(Resident $resident): array
    {
        return [
            'surname'                => $resident->surname ?? '',
            'first_name'             => $resident->first_name ?? '',
            'middle_name'            => $resident->middle_name ?? '',
            'suffix'                 => $resident->suffix ?? '',
            'mobile_no'              => $resident->mobile_no ?? '',
            'sex'                    => $resident->sex ?? '',
            'birth_date'             => $resident->birth_date ? $resident->birth_date->format('Y-m-d') : '',
            'birth_place'            => $resident->birth_place ?? '',
            'religion'               => $resident->religion ?? '',
            'citizenship'            => $resident->citizenship ?? '',
            'civil_status'           => $resident->civil_status ?? '',
            'spouse_name'            => $resident->spouse_name ?? '',
            'occupation'             => $resident->occupation ?? '',
            'educational_attainment' => $resident->educational_attainment ?? '',
        ] + $this->profile($resident);
    }

    // ===== Profile =====

    /**
     * A blank RBI profile, for a form that is creating a resident.
     *
     * @return array<string, mixed>
     */
    public function profileBlank(): array
    {
        return [
            'philsys_card_no' => '',
            'sectors' => [],
        ];
    }

    /**
     * The profile a saved resident carries, as the form holds it.
     *
     * @return array<string, mixed>
     */
    public function profile(Resident $resident): array
    {
        return [
            'philsys_card_no' => $resident->philsys_card_no ?? '',
            'sectors' => $resident->sectorValues(),
        ];
    }
}
