<div>
    {{-- =================================================================== --}}
    {{-- THE DAY'S COLLECTIONS, AND THE CONTROLS THAT PICK THE DAY            --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar subtitle="All payments received for the selected date (excluding applied advance deposits)">
        <x-slot name="filters">
            <x-adminlte-input name="date" type="date" wire:model="date" igroup-size="sm" fgroup-class="mb-0" />
            <span wire:loading wire:target="date" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    {{-- =================================================================== --}}
    {{-- WHAT THE DAY CAME TO, AND HOW THE MONEY ARRIVED                      --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-md-8 mb-3">
            <x-reports.kpi label="Total Collections" theme="success" icon="fas fa-fw fa-money-bill-wave"
                :value="\App\Utility\MoneyHelper::format($report['total'])"
                :note="'Date: ' . \Carbon\Carbon::parse($report['date'])->format('F j, Y')" />
        </div>
        <div class="col-12 col-md-4 mb-3">
            <x-reports.kpi label="No. of Transactions" theme="primary" icon="fas fa-fw fa-receipt"
                :value="$report['count']" />
        </div>
    </div>

    @php
        $methodSlices = [];

        foreach ($report['by_method'] as $method => $amount) {
            $methodSlices[] = ['label' => $method, 'value' => (float) $amount];
        }
    @endphp

    @if (! empty($methodSlices))
        <div class="card">
            <div class="card-header">
                <span class="font-weight-bold"><i class="fas fa-fw fa-wallet mr-1"></i> How the Money Arrived</span>
            </div>
            <div class="card-body">
                <x-reports.pie-chart :data="$methodSlices" money
                    :center="\App\Utility\MoneyHelper::formatShort($report['total'])" center-label="Today" />
            </div>
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- THE COLLECTION REGISTER — a table on a wide screen, a card per        --}}
    {{-- receipt on a small one                                                --}}
    {{-- =================================================================== --}}
    @php
        $cards = collect($report['rows'])->map(function ($row) {
            return [
                'title'    => $row['reference'],
                'subtitle' => ($row['property'] ? $row['property']->block_lot_label : '—') . ' · ' . $row['homeowner'],
                'facts'    => [
                    ['label' => 'Particulars', 'value' => $row['particulars']],
                    ['label' => 'Method', 'value' => $row['method']],
                ],
                'total' => ['label' => 'Total', 'value' => \App\Utility\MoneyHelper::format($row['amount'])],
            ];
        })->values()->all();

        if (! empty($report['rows'])) {
            $cards[] = [
                'title' => 'Total',
                'facts' => [['label' => 'Transactions', 'value' => $report['count'], 'strong' => true]],
                'total' => ['label' => 'Collected', 'value' => \App\Utility\MoneyHelper::format($report['total'])],
            ];
        }
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-clipboard-list mr-1"></i> Collection Register</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report table-sm small mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 16%;">Ref No.</th>
                        <th style="width: 12%;">Unit</th>
                        <th style="width: 20%;">Received From</th>
                        <th>Particulars</th>
                        <th style="width: 14%;">Method</th>
                        <th class="text-right" style="width: 14%;">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td class="align-middle">{{ $row['reference'] }}</td>
                            <td class="align-middle">
                                {{ $row['property'] ? $row['property']->block_lot_label : '—' }}
                            </td>
                            <td class="align-middle text-break">{{ $row['homeowner'] }}</td>
                            <td class="align-middle text-break">{{ $row['particulars'] }}</td>
                            <td class="align-middle">{{ $row['method'] }}</td>
                            <td class="text-right amount-mono align-middle">
                                {{ \App\Utility\MoneyHelper::format($row['amount']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                                <p class="mb-0">No payment was received on this date.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (! empty($report['rows']))
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right">TOTAL</td>
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['total']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <x-reports.cards class="card-body" :rows="$cards" empty="No payment was received on this date." />
    </div>
</div>
