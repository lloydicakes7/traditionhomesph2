<?php

namespace App\Services\Homeowners;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\SectorType;
use App\Models\Resident;

class SectoralTagService
{
    const MINIMUM_WORKING_AGE = 15;

    const MAXIMUM_WORKING_MINOR_AGE = 17;

    const OSC_MAXIMUM_ATTAINMENT = 'elementary_graduate';

    const OSY_MINIMUM_GRADUATE_ATTAINMENT = 'college_graduate';

    /**
     * What stops the tags from being saved, each as a sentence for the resident.
     *
     * @param  array<int, string>  $sectors
     * @param  array<string, mixed>  $facts
     * @return array<int, string>
     */
    public function errors(array $sectors, array $facts): array
    {
        $age = $facts['age'] ?? null;
        $errors = [];

        foreach ($sectors as $sector) {
            $type = SectorType::tryFrom((string) $sector);

            if ($type === null || $age === null) {
                continue;
            }

            $minimum = $type->minimumAge();
            $maximum = $type->maximumAge();

            if ($minimum > 0 && $age < $minimum && $type->value !== SectorType::SOLO_PARENT) {
                $errors[] = $type->label().' requires age '.$minimum.' or above.';
            }

            if ($maximum !== null && $age > $maximum) {
                $errors[] = $type->label().' is for ages '.$minimum.' to '.$maximum.'.';
            }
        }

        if ($this->has($sectors, SectorType::OFW) && ! $this->has($sectors, SectorType::LABOR_FORCE_EMPLOYED)) {
            $errors[] = 'Overseas Filipino Worker requires Labor Force / Employed.';
        }

        $hasOccupation = trim((string) ($facts['occupation'] ?? '')) !== '';

        if ($this->has($sectors, SectorType::LABOR_FORCE_EMPLOYED) && ! $hasOccupation) {
            $errors[] = 'Occupation is required for Labor Force / Employed.';
        }

        if ($hasOccupation && ! $this->has($sectors, SectorType::LABOR_FORCE_EMPLOYED)) {
            $errors[] = 'Labor Force / Employed must be checked when an occupation is given.';
        }

        if ($this->has($sectors, SectorType::SOLO_PARENT)
            && ($facts['civil_status'] ?? '') === CivilStatus::MARRIED
            && ! ($facts['solo_parent_special_case'] ?? false)) {
            $errors[] = 'A married solo parent must confirm the spouse is incapacitated, detained, or working abroad.';
        }

        return $errors;
    }

    /**
     * Every rule the tags break, plus what the office should know.
     *
     * @param  array<int, string>  $sectors
     * @param  array<string, mixed>  $facts
     * @return array<int, string>
     */
    public function warnings(array $sectors, array $facts): array
    {
        $age = $facts['age'] ?? null;
        $attainment = EducationalAttainment::tryFrom((string) ($facts['educational_attainment'] ?? ''));
        $warnings = $this->errors($sectors, $facts);

        if ($age !== null
            && $age >= self::MINIMUM_WORKING_AGE
            && $age <= self::MAXIMUM_WORKING_MINOR_AGE
            && ($this->has($sectors, SectorType::LABOR_FORCE_EMPLOYED) || $this->has($sectors, SectorType::UNEMPLOYED))) {
            $warnings[] = 'Working minor: a resident aged 15 to 17 needs a working permit.';
        }

        if ($age !== null && $age < Resident::MINIMUM_HEAD_AGE && $this->has($sectors, SectorType::SOLO_PARENT)) {
            $warnings[] = 'Solo parent below 18: allowed as a minor parent and flagged for the office.';
        }

        if ($attainment !== null && $this->has($sectors, SectorType::OSC)
            && $attainment->rank() > EducationalAttainment::rankOf(self::OSC_MAXIMUM_ATTAINMENT)) {
            $warnings[] = 'Out of School Children usually has no higher than elementary attainment.';
        }

        if ($attainment !== null && $this->has($sectors, SectorType::OSY)
            && $attainment->rank() >= EducationalAttainment::rankOf(self::OSY_MINIMUM_GRADUATE_ATTAINMENT)) {
            $warnings[] = 'Out of School Youth usually means not in school and without a job; a college graduate is unusual.';
        }

        return array_values(array_unique($warnings));
    }

    /**
     * @param  array<int, string>  $sectors
     */
    private function has(array $sectors, string $sector): bool
    {
        return in_array($sector, $sectors, true);
    }
}
