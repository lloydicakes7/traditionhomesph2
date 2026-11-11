<?php

namespace App\Queries\Reports;

use App\Models\Announcement;
use App\Models\Directory\DirectoryEntry;
use App\Models\HoaMember;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use Carbon\Carbon;

/**
 * The community snapshot — the association read as an organization: who uses
 * the portal, how much of its stock of units is occupied, how it has grown over
 * the year, and what the office keeps on file.
 *
 * What the shared facilities are doing is not read here: an amenity is its own
 * module's story and has its own report (see AmenityReportQuery), so this page
 * holds the figures about the association itself.
 *
 * The figures are shared with the page's parts rather than queried twice: the
 * records are one list the table and the chart both read, and the occupancy and
 * adoption pies are the unit and account counts the KPI row already carries.
 *
 * The portal's take-up is the one part of the snapshot that is about accounts
 * rather than people (`User`), and it is read here rather than left to the
 * Residents Report: this page is where the association's own figures are read,
 * and a chart of them belongs beside the counters that state them.
 */
class CommunityReportQuery
{
    /**
     * The community snapshot.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $portal = $this->portalUsage();

        return [
            'portal'        => $portal,
            'adoption'      => $this->adoption($portal),
            'occupancy'     => $this->occupancy($portal),
            'registrations' => $this->registrations(),
            'records'       => $this->records(),
        ];
    }

    /**
     * Who uses the portal: how many homeowners hold an account, how many are
     * active, and what the gateway is offered.
     *
     * @return array<string, mixed>
     */
    private function portalUsage(): array
    {
        $accounts = User::query()->count();
        $active = User::query()->where('is_active', true)->count();
        $residents = Resident::query()->count();
        $households = Household::query()->count();

        return [
            'accounts'        => $accounts,
            'active'          => $active,
            'deactivated'     => $accounts - $active,
            'residents'       => $residents,
            'residents_without_account' => max($residents - $accounts, 0),
            'households'      => $households,
            'adoption'        => $residents > 0 ? round($accounts / $residents * 100, 1) : 0.0,
            'properties'      => Property::query()->count(),
            'occupied'        => Property::query()->withHousehold()->count(),
        ];
    }

    /**
     * How the portal has been taken up, as the split drawn beside the KPI row.
     *
     * The two figures are the row's own counters — accounts on file, and the
     * residents profiled without one — so the chart and the numbers above it can
     * never disagree.
     *
     * @param  array<string, mixed>  $portal
     * @return array<int, array<string, mixed>>
     */
    private function adoption(array $portal): array
    {
        return [
            ['label' => 'With an account', 'value' => $portal['accounts'], 'theme' => 'success'],
            ['label' => 'No account yet', 'value' => $portal['residents_without_account'], 'theme' => 'secondary'],
        ];
    }

    /**
     * How many residents the office profiled in each month of the current year —
     * whether the community is still growing, read as a line.
     *
     * Every month is read in order, so a month nobody joined in is a zero on the
     * line rather than a point that fell off it.
     *
     * @return array<int, array<string, mixed>>
     */
    private function registrations(): array
    {
        $year = (int) now()->format('Y');
        $counts = array_fill(1, 12, 0);

        $joined = Resident::query()->whereYear('created_at', $year)->pluck('created_at');

        foreach ($joined as $joinedAt) {
            $counts[(int) Carbon::parse($joinedAt)->format('n')]++;
        }

        $points = [];

        for ($month = 1; $month <= 12; $month++) {
            $points[] = [
                'label' => now()->setDate($year, $month, 1)->format('M'),
                'value' => $counts[$month],
            ];
        }

        return $points;
    }

    /**
     * The association's stock of units, split the one way a reader asks about
     * it first: how much of it a household actually occupies.
     *
     * @param  array<string, mixed>  $portal
     * @return array<int, array<string, mixed>>
     */
    private function occupancy(array $portal): array
    {
        return [
            ['label' => 'Occupied', 'value' => $portal['occupied'], 'theme' => 'success'],
            ['label' => 'Vacant', 'value' => max($portal['properties'] - $portal['occupied'], 0), 'theme' => 'secondary'],
        ];
    }

    /**
     * What the association's own records hold — the community the office keeps
     * on file, as the page's table and its chart both read it.
     *
     * @return array<int, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            ['label' => 'Residents profiled', 'value' => Resident::query()->count()],
            ['label' => 'Households', 'value' => Household::query()->count()],
            ['label' => 'Properties', 'value' => Property::query()->count()],
            ['label' => 'HOA members', 'value' => HoaMember::query()->count()],
            ['label' => 'Directory entries', 'value' => DirectoryEntry::query()->count()],
            ['label' => 'Announcements published', 'value' => Announcement::query()->count()],
            ['label' => 'Announcements this year', 'value' => Announcement::query()
                ->whereYear('created_at', now()->format('Y'))
                ->count()],
        ];
    }
}
