<div>
    {{-- =================================================================== --}}
    {{-- THE PERIOD THE STATEMENT COVERS                                      --}}
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
    {{-- WHAT THE PERIOD CAME TO                                              --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-md-4 mb-3">
            <x-reports.kpi label="Total Income" theme="success" icon="fas fa-fw fa-arrow-down"
                :value="\App\Utility\MoneyHelper::format($report['total_revenue'])" />
        </div>
        <div class="col-12 col-md-4 mb-3">
            <x-reports.kpi label="Total Expenses" theme="danger" icon="fas fa-fw fa-arrow-up"
                :value="\App\Utility\MoneyHelper::format($report['total_expenses'])" />
        </div>
        <div class="col-12 col-md-4 mb-3">
            <x-reports.kpi :theme="$report['profitable'] ? 'primary' : 'warning'"
                icon="fas fa-fw fa-chart-line" label="Net Income"
                :value="\App\Utility\MoneyHelper::format($report['net_income'])"
                :note="$report['profitable'] ? 'Income covers expenses' : 'Expenses exceed income'" />
        </div>
    </div>

    @php
        $expenseSlices = [];

        foreach ($report['expenses'] as $row) {
            $amount = (float) $row['signed'];

            if ($amount > 0) {
                $expenseSlices[] = ['label' => $row['account']->name, 'value' => $amount];
            }
        }
    @endphp

    {{-- =================================================================== --}}
    {{-- WHAT IT EARNED AGAINST WHAT IT SPENT, AND WHERE THE SPENDING WENT     --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-bar mr-1"></i> Income vs Expenses</span>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart money :data="[
                        ['label' => 'Income', 'value' => $report['total_revenue'], 'theme' => 'success'],
                        ['label' => 'Expenses', 'value' => $report['total_expenses'], 'theme' => 'danger'],
                    ]" />
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <span class="font-weight-bold"><i class="fas fa-fw fa-chart-pie mr-1"></i> Where the Money Went</span>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$expenseSlices" money
                        :center="\App\Utility\MoneyHelper::formatShort($report['total_expenses'])" center-label="Expenses" />
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE STATEMENT: what was earned, what was spent, and the difference —  --}}
    {{-- a table on a wide screen, a card per account on a small one           --}}
    {{-- =================================================================== --}}
    @php
        $cards = [];

        foreach (['INCOME' => $report['revenue'], 'EXPENSES' => $report['expenses']] as $heading => $rows) {
            $groupTotal = $heading === 'INCOME' ? $report['total_revenue'] : $report['total_expenses'];

            foreach ($rows as $row) {
                $cards[] = [
                    'group'    => $heading,
                    'title'    => $row['account']->name,
                    'subtitle' => $row['account']->code,
                    'facts'    => [['label' => 'Amount', 'value' => \App\Utility\MoneyHelper::format($row['signed'])]],
                ];
            }

            $cards[] = [
                'group' => $heading,
                'title' => 'Total ' . $heading,
                'facts' => [['label' => 'Amount', 'value' => \App\Utility\MoneyHelper::format($groupTotal), 'strong' => true]],
            ];
        }

        $cards[] = [
            'title' => 'Net Income',
            'facts' => [[
                'label' => 'Amount',
                'value' => \App\Utility\MoneyHelper::format($report['net_income']),
                'strong' => true,
                'theme' => $report['net_income'] < 0 ? 'danger' : 'success',
            ]],
        ];
    @endphp

    <div class="card">
        <div class="card-header">
            <span class="font-weight-bold"><i class="fas fa-fw fa-chart-line mr-1"></i> Profit &amp; Loss Statement</span>
        </div>

        <div class="card-body p-0 report-table-view">
            <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 12%;">Code</th>
                        <th>Account</th>
                        <th class="text-right" style="width: 26%;">Amount</th>
                    </tr>
                </thead>

                @foreach (['INCOME' => $report['revenue'], 'EXPENSES' => $report['expenses']] as $heading => $rows)
                    <tbody>
                        <tr class="table-group-row">
                            <td colspan="3">{{ $heading }}</td>
                        </tr>

                        @forelse ($rows as $row)
                            <tr>
                                <td class="align-middle">{{ $row['account']->code }}</td>
                                <td class="align-middle text-break">{{ $row['account']->name }}</td>
                                <td class="text-right amount-mono align-middle">
                                    {{ \App\Utility\MoneyHelper::format($row['signed']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Nothing posted in the period.</td>
                            </tr>
                        @endforelse

                        <tr class="table-group-row">
                            <td colspan="2" class="text-right">TOTAL {{ $heading }}</td>
                            <td class="text-right amount-mono">
                                {{ \App\Utility\MoneyHelper::format(
                                    $heading === 'INCOME' ? $report['total_revenue'] : $report['total_expenses']
                                ) }}
                            </td>
                        </tr>
                    </tbody>
                @endforeach

                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">NET INCOME</td>
                        <td class="text-right amount-mono {{ $report['net_income'] < 0 ? 'text-danger' : 'text-success' }}">
                            {{ \App\Utility\MoneyHelper::format($report['net_income']) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <x-reports.cards class="card-body" :rows="$cards" empty="Nothing posted in the period." />
    </div>
</div>
