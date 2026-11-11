<div>
    <x-reports.toolbar subtitle="The association read as a population — age, household mix, background, and the units on file" :exportable="false" />

    {{-- =================================================================== --}}
    {{-- WHO IS ON FILE                                                       --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Residents on File" theme="secondary" icon="fas fa-fw fa-users"
                :value="$report['totals']['residents']"
                :note="$report['totals']['households'] . ' ' . \Illuminate\Support\Str::plural('household', $report['totals']['households'])" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Portal Accounts" theme="primary" icon="fas fa-fw fa-user-shield"
                :value="$report['totals']['with_account']"
                :note="$report['totals']['adoption'] . '% adopted'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Occupied Units" theme="info" icon="fas fa-fw fa-home"
                :value="$report['totals']['occupied']"
                :note="'of ' . $report['totals']['properties'] . ' properties'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Average Age" theme="success" icon="fas fa-fw fa-birthday-cake"
                :value="round($report['age_stats']['average']) . ' yrs'"
                :note="'median ' . round($report['age_stats']['median']) . ' · ' . round($report['age_stats']['min']) . '–' . round($report['age_stats']['max'])" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- WHO THEY ARE, HOW OLD THEY ARE, AND WHAT THEY LIVE IN                --}}
    {{--                                                                      --}}
    {{-- One card each, three to a row on a wide screen: the page holds nine   --}}
    {{-- of them, and a row that always fills is a grid with no empty space    --}}
    {{-- standing beside the last card. Below `lg` every card takes its own    --}}
    {{-- row, so no width leaves a card stranded either.                       --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-venus-mars mr-1"></i> Gender</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$report['gender']"
                        :center="$report['totals']['residents']" center-label="Residents" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-heart mr-1"></i> Civil Status</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$report['civil_status']" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Age Distribution</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['age_bands']" theme="info" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-simple mr-1"></i> Age Spread</span>
                    <span class="float-right text-muted small">Middle half, by sex</span>
                </div>
                <div class="card-body">
                    <x-reports.box-plot :data="$report['age_by_sex']" unit="yrs" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-globe mr-1"></i> Citizenship</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['citizenship']" orientation="horizontal" cycle />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-church mr-1"></i> Religion</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['religion']" orientation="horizontal" cycle />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-graduation-cap mr-1"></i> Educational Attainment</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['education']" orientation="horizontal" theme="info" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-house-user mr-1"></i> Household Type</span>
                    <span class="float-right text-muted small">{{ $report['totals']['properties'] }} units on file</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['household_type']" orientation="horizontal" cycle />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-building mr-1"></i> Property Type</span>
                    <span class="float-right text-muted small">{{ $report['totals']['properties'] }} units on file</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['property_type']" orientation="horizontal" cycle />
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE SAME FIGURES AS PLAIN NUMBERS — as the Community Report lists    --}}
    {{-- the association's own records: a table on a wide screen, the same     --}}
    {{-- labels and figures as a list on a small one                           --}}
    {{-- =================================================================== --}}
    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-folder-open mr-1"></i> Residents Report in Numbers</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th>Record</th>
                        <th class="text-right" style="width: 20%;">On File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['statistics'] as $row)
                        <tr>
                            <td class="align-middle text-break">{{ $row['label'] }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-reports.facts class="mx-3 mb-3" :rows="$report['statistics']" />
    </div>
</div>
