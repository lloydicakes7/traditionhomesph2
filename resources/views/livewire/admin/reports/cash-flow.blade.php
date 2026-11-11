<div>
    {{-- =================================================================== --}}
    {{-- THE PERIOD THE CASH MOVED IN                                         --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar :subtitle="'Period: ' . $periodLabel">
        <x-slot name="filters">
            <x-adminlte-input name="from" type="date" wire:model="from" igroup-size="sm" fgroup-class="mb-0" />
            <x-adminlte-input name="to" type="date" wire:model="to" igroup-size="sm" fgroup-class="mb-0" />
            <span wire:loading wire:target="from,to" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    {{-- =================================================================== --}}
    {{-- WHAT THE MONEY ACCOUNTS DID                                          --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-md-3 mb-3">
            <x-reports.kpi label="Opening Cash" theme="secondary"
                :value="\App\Utility\MoneyHelper::format($report['opening'])" />
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-reports.kpi label="Money In" theme="success"
                :value="\App\Utility\MoneyHelper::format($report['total_in'])" />
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-reports.kpi label="Money Out" theme="danger"
                :value="\App\Utility\MoneyHelper::format($report['total_out'])" />
        </div>
        <div class="col-6 col-md-3 mb-3">
            <x-reports.kpi label="Closing Cash" :theme="$report['increased'] ? 'primary' : 'warning'"
                :value="\App\Utility\MoneyHelper::format($report['closing'])"
                :note="($report['increased'] ? 'Up ' : 'Down ') . \App\Utility\MoneyHelper::format(abs($report['net_change']))" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE PERIOD AS A SHAPE — what came in against what went out           --}}
    {{-- =================================================================== --}}
    @if (! empty($report['rows']))
        <div class="card">
            <div class="card-header">
                <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Money In and Out</span>
            </div>
            <div class="card-body">
                <x-reports.bar-chart money :data="[
                    ['label' => 'Opening', 'value' => $report['opening'], 'theme' => 'secondary'],
                    ['label' => 'Money In', 'value' => $report['total_in'], 'theme' => 'success'],
                    ['label' => 'Money Out', 'value' => $report['total_out'], 'theme' => 'danger'],
                    ['label' => 'Closing', 'value' => $report['closing'], 'theme' => 'primary'],
                ]" />
            </div>
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- WHERE THE MONEY SITS, AND HOW IT MOVED — a table on a wide screen,    --}}
    {{-- a card per account on a small one                                     --}}
    {{-- =================================================================== --}}
    @php
        $cards = collect($report['rows'])->map(function ($row) {
            return [
                'title' => $row['account']->label,
                'facts' => [
                    ['label' => 'Opening', 'value' => \App\Utility\MoneyHelper::format($row['opening'])],
                    ['label' => 'Money In', 'value' => \App\Utility\MoneyHelper::format($row['in']), 'theme' => 'success'],
                    ['label' => 'Money Out', 'value' => \App\Utility\MoneyHelper::format($row['out']), 'theme' => 'danger'],
                ],
                'total' => ['label' => 'Closing', 'value' => \App\Utility\MoneyHelper::format($row['closing'])],
            ];
        })->values()->all();

        if (! empty($report['rows'])) {
            $cards[] = [
                'title' => 'Total',
                'facts' => [
                    ['label' => 'Opening', 'value' => \App\Utility\MoneyHelper::format($report['opening']), 'strong' => true],
                    ['label' => 'Money In', 'value' => \App\Utility\MoneyHelper::format($report['total_in']), 'strong' => true],
                    ['label' => 'Money Out', 'value' => \App\Utility\MoneyHelper::format($report['total_out']), 'strong' => true],
                ],
                'total' => ['label' => 'Closing', 'value' => \App\Utility\MoneyHelper::format($report['closing'])],
            ];
        }
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-exchange-alt mr-1"></i> Cash Flow Statement</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th class="text-right" style="width: 15%;">Opening</th>
                        <th class="text-right" style="width: 15%;">Money In</th>
                        <th class="text-right" style="width: 15%;">Money Out</th>
                        <th class="text-right" style="width: 15%;">Closing</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td class="align-middle text-break">{{ $row['account']->label }}</td>
                            <td class="text-right amount-mono align-middle">{{ \App\Utility\MoneyHelper::format($row['opening']) }}</td>
                            <td class="text-right amount-mono align-middle text-success">{{ \App\Utility\MoneyHelper::format($row['in']) }}</td>
                            <td class="text-right amount-mono align-middle text-danger">{{ \App\Utility\MoneyHelper::format($row['out']) }}</td>
                            <td class="text-right amount-mono font-weight-bold align-middle">
                                {{ \App\Utility\MoneyHelper::format($row['closing']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-money-bill-wave fa-2x mb-2"></i>
                                <p class="mb-0">No cash account moved in this period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (! empty($report['rows']))
                    <tfoot>
                        <tr>
                            <td>TOTAL</td>
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['opening']) }}</td>
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['total_in']) }}</td>
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['total_out']) }}</td>
                            <td class="text-right amount-mono">{{ \App\Utility\MoneyHelper::format($report['closing']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <x-reports.cards class="card-body" :rows="$cards" empty="No cash account moved in this period." />
    </div>
</div>
