<div>
    <x-reports.toolbar subtitle="Portal usage, the units on file, and the association's own records" :exportable="false" />

    {{-- =================================================================== --}}
    {{-- WHO USES THE PORTAL                                                  --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Portal Accounts" theme="primary" icon="fas fa-fw fa-user-shield"
                :value="$report['portal']['accounts']"
                :note="$report['portal']['adoption'] . '% of residents profiled'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Active Accounts" theme="success" icon="fas fa-fw fa-user-check"
                :value="$report['portal']['active']"
                :note="$report['portal']['deactivated'] . ' deactivated'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Residents on File" theme="secondary" icon="fas fa-fw fa-users"
                :value="$report['portal']['residents']"
                :note="$report['portal']['residents_without_account'] . ' without an account'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Occupied Units" theme="info" icon="fas fa-fw fa-home"
                :value="$report['portal']['occupied']"
                :note="'of ' . $report['portal']['properties'] . ' properties'" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE ASSOCIATION'S OWN FIGURES, DRAWN                                 --}}
    {{--                                                                      --}}
    {{-- Four cards, two to a row on a wide screen and one per row below it,   --}}
    {{-- so a row never runs out of cards: how the stock of units is taken up, --}}
    {{-- how the portal has been, how the community grew over the year, and    --}}
    {{-- what the office keeps on file.                                        --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-home mr-1"></i> Unit Occupancy</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$report['occupancy']"
                        :center="$report['portal']['properties']" center-label="Units" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-user-check mr-1"></i> Portal Adoption</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$report['adoption']"
                        :center="$report['portal']['residents']" center-label="Residents" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-line mr-1"></i> Residents Profiled This Year</span>
                    <span class="float-right text-muted small">{{ now()->format('Y') }}</span>
                </div>
                <div class="card-body">
                    <x-reports.line-chart :data="$report['registrations']" theme="primary" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Records on File</span>
                    <span class="float-right text-muted small">All time</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$report['records']" orientation="horizontal" cycle />
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- WHAT THE ASSOCIATION'S OWN RECORDS HOLD — a table on a wide screen,   --}}
    {{-- the same labels and figures as a list on a small one                  --}}
    {{-- =================================================================== --}}
    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-folder-open mr-1"></i> Association Records</span>
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
                    @foreach ($report['records'] as $row)
                        <tr>
                            <td class="align-middle text-break">{{ $row['label'] }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-reports.facts class="mx-3 mb-3" :rows="$report['records']" />
    </div>
</div>
