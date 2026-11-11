<div>
    {{-- =================================================================== --}}
    {{-- WHAT THE REPORT READS: both kinds of bill, narrowed to a unit        --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar :subtitle="'Uncollected homeowner invoices and dues as of ' . \Carbon\Carbon::parse($report['as_of'])->format('F j, Y')">
        <x-slot name="filters">
            <div class="report-filter-field">
                <x-adminlte-input name="search" placeholder="Search by property or homeowner..."
                    wire:model.debounce.400ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="report-filter-field-sm">
                <x-adminlte-input name="block" placeholder="Block..."
                    wire:model.debounce.400ms="block" igroup-size="sm" fgroup-class="mb-0" />
            </div>
            <div class="report-filter-field-sm">
                <x-adminlte-input name="lot" placeholder="Lot..."
                    wire:model.debounce.400ms="lot" igroup-size="sm" fgroup-class="mb-0" />
            </div>

            <div class="report-filter-field-sm">
                <x-adminlte-input name="asOf" type="date" wire:model="asOf" igroup-size="sm" fgroup-class="mb-0" />
            </div>

            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="agingIncludeDues" wire:model="includeDues">
                <label class="form-check-label" for="agingIncludeDues">Monthly Dues</label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="agingIncludeInvoices" wire:model="includeInvoices">
                <label class="form-check-label" for="agingIncludeInvoices">All Invoices</label>
            </div>

            <span wire:loading wire:target="asOf,includeDues,includeInvoices,search,block,lot" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    {{-- =================================================================== --}}
    {{-- HOW FAR THE OUTSTANDING MONEY HAS RUN — two on a phone, three on a    --}}
    {{-- tablet, one row on a wide screen                                     --}}
    {{-- =================================================================== --}}
    <div class="row">
        @foreach ($report['buckets'] as $bucket)
            <div class="col-6 col-md-4 col-xl mb-3">
                <x-reports.kpi :label="$bucket->label()" :theme="$bucket->badgeTheme()"
                    :value="\App\Utility\MoneyHelper::format($report['totals'][$bucket->value])" />
            </div>
        @endforeach

        <div class="col-12 col-md-4 col-xl mb-3">
            <x-reports.kpi label="Total Outstanding" theme="success"
                :value="\App\Utility\MoneyHelper::format($report['total'])"
                :note="$report['count'] . ' ' . \Illuminate\Support\Str::plural('unit', $report['count'])" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE SAME MONEY AS A SHAPE — which bucket carries the weight          --}}
    {{-- =================================================================== --}}
    @php
        $bucketBars = [];

        foreach ($report['buckets'] as $bucket) {
            $bucketBars[] = [
                'label' => $bucket->label(),
                'value' => $report['totals'][$bucket->value],
            ];
        }
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Outstanding by Age</span>
            <span class="float-right text-muted small">Total {{ \App\Utility\MoneyHelper::format($report['total']) }}</span>
        </div>
        <div class="card-body">
            <x-reports.bar-chart :data="$bucketBars" orientation="horizontal" money cycle />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- EVERY UNIT'S UNCOLLECTED BILLS, IN THE BUCKETS THEY HAVE SAT IN —    --}}
    {{-- a table on a wide screen, a card per unit on a small one              --}}
    {{-- =================================================================== --}}
    @php
        $cards = collect($report['rows'])->map(function ($row) use ($report) {
            $facts = [
                ['label' => 'Bills', 'value' => $row['bills']],
            ];

            $facts[] = $row['oldest_bucket']->isOverdue()
                ? ['label' => 'Oldest', 'value' => $row['oldest_days'] . ' ' . \Illuminate\Support\Str::plural('day', $row['oldest_days']), 'badge' => $row['oldest_bucket']->badgeTheme()]
                : ['label' => 'Oldest', 'value' => $row['oldest_bucket']->label(), 'theme' => 'muted'];

            foreach ($report['buckets'] as $bucket) {
                $amount = $row['buckets'][$bucket->value];

                if ($amount > 0) {
                    $facts[] = ['label' => $bucket->label(), 'value' => \App\Utility\MoneyHelper::format($amount)];
                }
            }

            return [
                'title'    => $row['property'] ? $row['property']->block_lot_label : '—',
                'subtitle' => $row['homeowner'],
                'facts'    => $facts,
                'total'    => ['label' => 'Total', 'value' => \App\Utility\MoneyHelper::format($row['total'])],
            ];
        })->values()->all();

        if (! empty($report['rows'])) {
            $totals = [['label' => 'Units', 'value' => $report['count'], 'strong' => true]];

            foreach ($report['buckets'] as $bucket) {
                $totals[] = [
                    'label' => $bucket->label(),
                    'value' => \App\Utility\MoneyHelper::format($report['totals'][$bucket->value]),
                    'strong' => true,
                ];
            }

            $cards[] = [
                'title' => 'Total Outstanding',
                'facts' => $totals,
                'total' => ['label' => 'Total', 'value' => \App\Utility\MoneyHelper::format($report['total'])],
            ];
        }
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-history mr-1"></i> Uncollected Bills by Age</span>
            <span class="float-right text-muted small">Oldest: how long the unit's oldest bill has run</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report table-sm small mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 22%;">Unit</th>
                        <th style="width: 12%;">Oldest</th>
                        @foreach ($report['buckets'] as $bucket)
                            <th class="text-right">{{ $bucket->label() }}</th>
                        @endforeach
                        <th class="text-right" style="width: 14%;">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold">
                                    {{ $row['property'] ? $row['property']->block_lot_label : '—' }}
                                </div>
                                <div class="text-muted text-break">
                                    {{ $row['homeowner'] }} ·
                                    {{ $row['bills'] }} {{ \Illuminate\Support\Str::plural('bill', $row['bills']) }}
                                </div>
                            </td>
                            <td class="align-middle">
                                @if ($row['oldest_bucket']->isOverdue())
                                    <span class="badge badge-{{ $row['oldest_bucket']->badgeTheme() }}">
                                        {{ $row['oldest_days'] }} {{ \Illuminate\Support\Str::plural('day', $row['oldest_days']) }}
                                    </span>
                                @else
                                    <span class="text-muted small">{{ $row['oldest_bucket']->label() }}</span>
                                @endif
                            </td>
                            @foreach ($report['buckets'] as $bucket)
                                <td class="text-right amount-mono align-middle">
                                    {{ $row['buckets'][$bucket->value] > 0
                                        ? \App\Utility\MoneyHelper::format($row['buckets'][$bucket->value])
                                        : '—' }}
                                </td>
                            @endforeach
                            <td class="text-right amount-mono font-weight-bold align-middle">
                                {{ \App\Utility\MoneyHelper::format($row['total']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['buckets']) + 3 }}" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-check-circle fa-2x mb-2"></i>
                                <p class="mb-0">
                                    Nothing is outstanding{{ $search || $block || $lot ? ' for that filter' : '' }} —
                                    every unit is settled.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (! empty($report['rows']))
                    <tfoot>
                        <tr>
                            <td colspan="2">TOTAL</td>
                            @foreach ($report['buckets'] as $bucket)
                                <td class="text-right amount-mono">
                                    {{ \App\Utility\MoneyHelper::format($report['totals'][$bucket->value]) }}
                                </td>
                            @endforeach
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['total']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <x-reports.cards class="card-body" :rows="$cards"
            empty="Nothing is outstanding — every unit is settled." />
    </div>
</div>
