<div>
    <x-reports.toolbar subtitle="How the shared facilities are booked, what each is booked for, and what each has earned" :exportable="false" />

    {{-- =================================================================== --}}
    {{-- WHAT THE BOOKINGS COME TO                                            --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Bookings on File" theme="primary" icon="fas fa-fw fa-calendar-check"
                :value="$report['total']"
                :note="$report['this_year'] . ' booked in ' . $report['year']" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Upcoming" theme="info" icon="fas fa-fw fa-hourglass-half"
                :value="$report['upcoming']"
                note="Still ahead as of today" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Amenities on File" theme="secondary" icon="fas fa-fw fa-swimmer"
                :value="count($report['by_amenity'])"
                note="Facilities the office books" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Amenity Earnings" theme="success" icon="fas fa-fw fa-money-bill-wave"
                :value="$report['earnings']"
                note="Earned on completed bookings" />
        </div>
    </div>

    @php
        $statusSlices = collect($report['by_status'])
            ->filter(function ($status) { return $status['count'] > 0; })
            ->map(function ($status) { return ['label' => $status['label'], 'value' => $status['count']]; })
            ->values()
            ->all();

        $amenityBars = collect($report['by_amenity'])
            ->map(function ($row) { return ['label' => $row['amenity']->name, 'value' => $row['bookings']]; })
            ->all();
    @endphp

    <div class="row">
        {{-- ============================================================== --}}
        {{-- HOW THE BOOKINGS STAND TODAY                                   --}}
        {{-- ============================================================== --}}
        <div class="col-12 col-lg-5 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-pie mr-1"></i> Bookings by Status</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$statusSlices"
                        :center="$report['total']" center-label="Bookings" />
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- WHICH AMENITY IS BOOKED MOST                                   --}}
        {{-- ============================================================== --}}
        <div class="col-12 col-lg-7 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Bookings per Amenity</span>
                    <span class="float-right text-muted small">All time</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$amenityBars" orientation="horizontal" cycle />
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- HOW BUSY, HOW BOOKED AHEAD, AND WHAT IT EARNED — one bubble each     --}}
    {{-- =================================================================== --}}
    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-braille mr-1"></i> Amenity Performance</span>
            <span class="float-right text-muted small">Across: bookings &middot; Up: upcoming &middot; Size: earned</span>
        </div>
        <div class="card-body">
            <x-reports.bubble-chart :data="$report['bubbles']"
                x-label="Bookings" y-label="Upcoming" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE SAME FIGURES AS A LIST — a table on a wide screen, a card per     --}}
    {{-- amenity on a small one, as the Community Report lists its own         --}}
    {{-- =================================================================== --}}
    @php
        $cards = collect($report['by_amenity'])->map(function ($row) {
            return [
                'title' => $row['amenity']->name,
                'facts' => [
                    ['label' => 'Bookings', 'value' => $row['bookings']],
                    ['label' => 'This Year', 'value' => $row['this_year']],
                    ['label' => 'Upcoming', 'value' => $row['upcoming']],
                ],
                'total' => ['label' => 'Earned', 'value' => $row['earnings']],
            ];
        })->values()->all();

        if (! empty($report['by_amenity'])) {
            $cards[] = [
                'title' => 'Total (all time)',
                'facts' => [
                    ['label' => 'Bookings', 'value' => $report['total'], 'strong' => true],
                    ['label' => 'This Year', 'value' => $report['this_year'], 'strong' => true],
                    ['label' => 'Upcoming', 'value' => $report['upcoming'], 'strong' => true],
                ],
                'total' => ['label' => 'Earned', 'value' => $report['earnings']],
            ];
        }
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-calendar-check mr-1"></i> Amenity Bookings</span>
            <span class="float-right text-muted small">{{ $report['year'] }}</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th>Amenity</th>
                        <th class="text-right" style="width: 16%;">Bookings</th>
                        <th class="text-right" style="width: 16%;">This Year</th>
                        <th class="text-right" style="width: 16%;">Upcoming</th>
                        <th class="text-right" style="width: 18%;">Earned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['by_amenity'] as $row)
                        <tr>
                            <td class="align-middle text-break">{{ $row['amenity']->name }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['bookings'] }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['this_year'] }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['upcoming'] }}</td>
                            <td class="text-right amount-mono align-middle">{{ $row['earnings'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No amenity is on file yet.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if (! empty($report['by_amenity']))
                    <tfoot>
                        <tr>
                            <td>TOTAL (all time)</td>
                            <td class="text-right amount-mono">{{ $report['total'] }}</td>
                            <td class="text-right amount-mono">{{ $report['this_year'] }}</td>
                            <td class="text-right amount-mono">{{ $report['upcoming'] }}</td>
                            <td class="text-right amount-mono">{{ $report['earnings'] }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <x-reports.cards class="card-body" :rows="$cards" empty="No amenity is on file yet." />
    </div>
</div>
