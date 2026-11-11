<?php

namespace App\Queries\Reports;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Enums\Sex;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use Illuminate\Support\Collection;

/**
 * Who the association is made of — the community read as a population rather
 * than a list.
 *
 * The Homeowners pages answer "who lives at Blk 7 Lot 12"; this answers "how
 * old is the subdivision, what do its households look like, where did its people
 * come from" — the questions a board asks when it is planning something. Every
 * figure is drawn from the same `residents`, `households` and `properties`
 * records the Homeowners module writes, so the report can never disagree with
 * the list it summarizes.
 *
 * What the community holds and how the portal has been taken up are the
 * Community Report's readings, and how it grew over the year is drawn there too
 * — this page is about the people themselves.
 *
 * The records are read once and counted in PHP rather than by a database
 * aggregation per question. That keeps every distribution on one consistent
 * basis — the same collection answers all of them — and keeps the report
 * readable on the SQLite the test suite runs on as well as MySQL.
 *
 * Two distributions are counted over a different record on purpose: the
 * property mix and the household mix are the association's own stock of units,
 * so both are counted over `properties` and not over the residents. A unit's
 * type and how its household holds it are facts about the unit, not about the
 * family living in it — and counted over residents, one owner-occupied house
 * with four members on file would read as four owners, which is not what the
 * office recorded. The office records one household per unit, through the
 * homeowner's own account (`households.user_id`, the `User` model), so a
 * household member is never counted as the owner of the unit.
 *
 * It also keeps the report whole where an association has not profiled every
 * unit yet: houses, townhouses and condos on file with no household recorded
 * still have to appear in its own report.
 */
class ResidentReportQuery
{
    /**
     * The age bands the population is read in — wide enough that a band is a
     * life stage rather than a year, which is what a household profile is for.
     *
     * @var array<string, array{0: int, 1: int|null}>
     */
    const AGE_BANDS = [
        '0–17'  => [0, 17],
        '18–29' => [18, 29],
        '30–39' => [30, 39],
        '40–49' => [40, 49],
        '50–59' => [50, 59],
        '60–69' => [60, 69],
        '70+'   => [70, null],
    ];

    /**
     * How many categories a distribution shows before the rest are gathered
     * into "Other" — a pie of forty religions is a pie nobody can read.
     */
    const DISTRIBUTION_LIMIT = 8;

    /**
     * The residents snapshot, ready for the page.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $residents = Resident::query()->get();
        $properties = Property::query()->with('currentHousehold')->get();

        $totals = $this->totals($residents);
        $ages = $this->ages($residents);

        return [
            'totals'         => $totals,
            'statistics'     => $this->statistics($totals),
            'gender'         => $this->distribution($residents, function (Resident $resident) {
                return Sex::tryFrom($resident->sex)?->label();
            }),
            'civil_status'   => $this->distribution($residents, function (Resident $resident) {
                return CivilStatus::tryFrom($resident->civil_status)?->label();
            }),
            'religion'       => $this->distribution($residents, function (Resident $resident) {
                return $resident->religion;
            }),
            'citizenship'    => $this->distribution($residents, function (Resident $resident) {
                return $resident->citizenship;
            }),
            'education'      => $this->distribution($residents, function (Resident $resident) {
                return EducationalAttainment::tryFrom($resident->educational_attainment)?->label();
            }),
            'household_type' => $this->distribution($properties, function (Property $property) {
                return HouseholdType::tryFrom($property->currentHousehold?->type)?->label();
            }),
            'property_type'  => $this->distribution($properties, function (Property $property) {
                return PropertyType::tryFrom($property->type)?->label();
            }),
            'age_bands'      => $this->ageBands($ages),
            'age_stats'      => $this->quartiles($ages),
            'age_by_sex'     => $this->ageBySex($residents),
        ];
    }

    /**
     * The headline counts the page opens with.
     *
     * @param  Collection<int, Resident>  $residents
     * @return array<string, mixed>
     */
    private function totals(Collection $residents): array
    {
        $withAccount = $residents->filter(function (Resident $resident) {
            return $resident->user !== null;
        })->count();

        $total = $residents->count();

        return [
            'residents'       => $total,
            'with_account'    => $withAccount,
            'without_account' => max($total - $withAccount, 0),
            'active_accounts' => $residents->filter(function (Resident $resident) {
                return $resident->user && $resident->user->is_active;
            })->count(),
            'adoption'        => $total > 0 ? round($withAccount / $total * 100, 1) : 0.0,
            'households'      => Household::query()->count(),
            'properties'      => Property::query()->count(),
            'occupied'        => Property::query()->withHousehold()->count(),
        ];
    }

    /**
     * The headline figures as a plain list of numbers — what the page's own
     * table shows, written the way the Community Report lists the association's
     * records.
     *
     * @param  array<string, mixed>  $totals
     * @return array<int, array<string, mixed>>
     */
    private function statistics(array $totals): array
    {
        return [
            ['label' => 'Residents profiled', 'value' => $totals['residents']],
            ['label' => 'Portal accounts', 'value' => $totals['with_account']],
            ['label' => 'Active accounts', 'value' => $totals['active_accounts']],
            ['label' => 'Residents without an account', 'value' => $totals['without_account']],
            ['label' => 'Households on file', 'value' => $totals['households']],
            ['label' => 'Properties on file', 'value' => $totals['properties']],
            ['label' => 'Occupied units', 'value' => $totals['occupied']],
            ['label' => 'Vacant units', 'value' => max($totals['properties'] - $totals['occupied'], 0)],
        ];
    }

    /**
     * The residents split by sex — the dashboard's donut of homeowners, read
     * from the same records and on the same basis as the Residents Report's
     * own gender distribution, so the two can never disagree.
     *
     * @return array<int, array<string, mixed>>
     */
    public function genderDistribution(): array
    {
        return $this->distribution(
            Resident::query()->get(),
            function (Resident $resident) {
                return Sex::tryFrom($resident->sex)?->label();
            }
        );
    }

    /**
     * A count by category, largest first, with everything past the limit
     * gathered into "Other" so a long tail stays readable.
     *
     * The records are whatever the question is about — the residents for most
     * of the report, the units on file for the property mix — and the callable
     * answers the category for one of them.
     *
     * @param  Collection<int, mixed>  $records
     * @return array<int, array<string, mixed>>
     */
    private function distribution(Collection $records, callable $label): array
    {
        $counts = [];

        foreach ($records as $record) {
            $key = $label($record) ?: 'Not specified';

            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        arsort($counts);

        $rows = [];

        foreach ($counts as $key => $count) {
            $rows[] = ['label' => $key, 'value' => $count];
        }

        if (count($rows) > self::DISTRIBUTION_LIMIT) {
            $tail = array_splice($rows, self::DISTRIBUTION_LIMIT - 1);

            $rows[] = [
                'label' => 'Other',
                'value' => array_sum(array_column($tail, 'value')),
            ];
        }

        return $rows;
    }

    /**
     * The population by age band, in band order.
     *
     * @param  array<int, int>  $ages
     * @return array<int, array<string, mixed>>
     */
    private function ageBands(array $ages): array
    {
        $rows = [];

        foreach (self::AGE_BANDS as $band => [$from, $to]) {
            $count = count(array_filter($ages, function ($age) use ($from, $to) {
                return $age >= $from && ($to === null || $age <= $to);
            }));

            $rows[] = ['label' => $band, 'value' => $count];
        }

        return $rows;
    }

    /**
     * The spread of ages, as a box plot series per sex — so it is plain whether
     * the association skews to one generation or holds every generation.
     *
     * @param  Collection<int, Resident>  $residents
     * @return array<int, array<string, mixed>>
     */
    private function ageBySex(Collection $residents): array
    {
        $series = [];

        foreach (Sex::cases() as $sex) {
            $ages = $this->ages($residents->filter(function (Resident $resident) use ($sex) {
                return $resident->sex === $sex->value;
            }));

            if (empty($ages)) {
                continue;
            }

            $series[] = array_merge(
                ['label' => $sex->label(), 'theme' => $sex->badgeTheme()],
                $this->quartiles($ages)
            );
        }

        return $series;
    }

    /**
     * The ages of the residents that have a birth date on file.
     *
     * @param  Collection<int, Resident>  $residents
     * @return array<int, int>
     */
    private function ages(Collection $residents): array
    {
        return $residents
            ->map(function (Resident $resident) {
                return $resident->age;
            })
            ->filter(function ($age) {
                return $age !== null;
            })
            ->values()
            ->all();
    }

    /**
     * The minimum, quartiles, maximum and average of a set of figures — what a
     * box plot is drawn from.
     *
     * @param  array<int, int>  $values
     * @return array<string, float>
     */
    private function quartiles(array $values): array
    {
        $count = count($values);

        if ($count === 0) {
            return ['min' => 0.0, 'q1' => 0.0, 'median' => 0.0, 'q3' => 0.0, 'max' => 0.0, 'average' => 0.0];
        }

        sort($values);

        $at = function (float $percentile) use ($values, $count) {
            $position = ($count - 1) * $percentile;
            $lower = (int) floor($position);
            $upper = (int) ceil($position);

            if ($lower === $upper) {
                return (float) $values[$lower];
            }

            return $values[$lower] + ($values[$upper] - $values[$lower]) * ($position - $lower);
        };

        return [
            'min'     => (float) $values[0],
            'q1'      => round($at(0.25), 1),
            'median'  => round($at(0.5), 1),
            'q3'      => round($at(0.75), 1),
            'max'     => (float) $values[$count - 1],
            'average' => round(array_sum($values) / $count, 1),
        ];
    }

}
