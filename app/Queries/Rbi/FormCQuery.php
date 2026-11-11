<?php

namespace App\Queries\Rbi;

use App\Enums\RbiSemester;
use App\Enums\SectorType;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Resident;

/**
 * What RBI Form C prints for one semester.
 *
 * The sheet is the barangay's semestral monitoring report: how many inhabitants
 * there are, counted per sex by age bracket, by sector, by civil status and by
 * citizenship. The figures are read live when the report is generated; the
 * semester and the year only label the sheet.
 */
class FormCQuery
{
    /**
     * The age brackets the revised form prints, in its own order.
     *
     * @var array<string, array{0: int, 1: int|null}>
     */
    const AGE_BRACKETS = [
        'under_5' => [0, 4],
        '5_9' => [5, 9],
        '10_14' => [10, 14],
        '15_19' => [15, 19],
        '20_24' => [20, 24],
        '25_29' => [25, 29],
        '30_34' => [30, 34],
        '35_39' => [35, 39],
        '40_44' => [40, 44],
        '45_49' => [45, 49],
        '50_54' => [50, 54],
        '55_59' => [55, 59],
        '60_64' => [60, 64],
        '65_69' => [65, 69],
        '70_74' => [70, 74],
        '75_79' => [75, 79],
        '80_over' => [80, null],
    ];

    // ===== Queries =====

    /**
     * The data Form C prints for a semester.
     *
     * @return array<string, mixed>
     */
    public function sheet(int $year, RbiSemester $semester): array
    {
        $residents = $this->residents();
        $address = Address::query()->orderBy('id')->first();

        return [
            'year' => $year,
            'semester' => __('rbi.form_c.' . ($semester->value === RbiSemester::FIRST ? 'first_semester' : 'second_semester')),
            'region' => $this->addressPart($address, 'region'),
            'province' => $this->addressPart($address, 'province'),
            'city_municipality' => $this->addressPart($address, 'city'),
            'barangay' => $this->addressPart($address, 'barangay'),
            'total_inhabitants' => (string) $residents->count(),
            'total_households' => (string) Household::query()->count(),
            'total_families' => (string) Household::query()->count(),
            'age_brackets' => $this->ageBrackets($residents),
            'sectors' => $this->sectors($residents),
            'civil_status' => [
                'single' => $this->row($residents->filter(function (Resident $resident) {
                    return (string) $resident->civil_status === 'single';
                })),
                'married' => $this->row($residents->filter(function (Resident $resident) {
                    return (string) $resident->civil_status === 'married';
                })),
            ],
            'citizenship' => [
                'filipino' => $this->row($residents->filter(function (Resident $resident) {
                    return stripos((string) $resident->citizenship, 'filipino') !== false;
                })),
                'foreigner' => $this->row($residents->filter(function (Resident $resident) {
                    return stripos((string) $resident->citizenship, 'filipino') === false;
                })),
            ],
            'date_accomplished' => '',
        ];
    }

    // ===== Helpers =====

    /**
     * Every resident living in a household, head and members, once each.
     *
     * @return \Illuminate\Support\Collection<int, Resident>
     */
    private function residents()
    {
        $residents = collect();
        $seen = [];

        $households = Household::query()
            ->with(['user.resident', 'memberRecords.resident'])
            ->get();

        foreach ($households as $household) {
            $candidates = array_merge(
                [$household->user ? $household->user->resident : null],
                $household->memberRecords->pluck('resident')->all()
            );

            foreach ($candidates as $resident) {
                if (! $resident instanceof Resident || isset($seen[$resident->id])) {
                    continue;
                }

                $seen[$resident->id] = true;
                $residents->push($resident);
            }
        }

        return $residents;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Resident>  $residents
     * @return array<string, array{male: int, female: int, total: int, remarks: string}>
     */
    private function ageBrackets($residents): array
    {
        $brackets = [];

        foreach (self::AGE_BRACKETS as $key => $range) {
            $brackets[$key] = $this->row($residents->filter(function (Resident $resident) use ($range) {
                return $this->inAgeRange($resident, $range[0], $range[1]);
            }));
        }

        return $brackets;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Resident>  $residents
     * @return array<string, array{male: int, female: int, total: int, remarks: string}>
     */
    private function sectors($residents): array
    {
        $sectors = [];

        foreach (SectorType::reportLabels() as $key => $label) {
            $sectors[$key] = $this->row($residents->filter(function (Resident $resident) use ($key) {
                return $this->inSector($resident, $key);
            }));
        }

        return $sectors;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Resident>  $residents
     * @return array{male: int, female: int, total: int, remarks: string}
     */
    private function row($residents): array
    {
        $male = $residents->filter(function (Resident $resident) {
            return (string) $resident->sex === 'male';
        })->count();

        $female = $residents->filter(function (Resident $resident) {
            return (string) $resident->sex === 'female';
        })->count();

        return [
            'male' => $male,
            'female' => $female,
            'total' => $male + $female,
            'remarks' => '',
        ];
    }

    private function addressPart(?Address $address, string $part): string
    {
        $value = $address ? $address->{$part} : null;

        return $value ?: (string) config('thha.locale.' . $part, '');
    }

    private function inAgeRange(Resident $resident, int $from, ?int $to): bool
    {
        $age = $resident->age;

        if ($age === null || $age < $from) {
            return false;
        }

        return $to === null || $age <= $to;
    }

    private function inSector(Resident $resident, string $sector): bool
    {
        $type = SectorType::tryFrom($sector);

        if ($type !== null && ! $type->appliesToAge($resident->age)) {
            return false;
        }

        return in_array($sector, $resident->sectorValues(), true);
    }
}
