<div>
    {{-- =================================================================== --}}
    {{-- THE YEAR, AND WHAT IT CAME TO                                        --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar subtitle="Annual income breakdown by month and account">
        <x-slot name="filters">
            <div class="report-filter-field">
                <x-adminlte-select name="year" wire:model="year" igroup-size="sm" fgroup-class="mb-0">
                    @foreach ($years as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <span wire:loading wire:target="year" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    {{-- =================================================================== --}}
    {{-- THE YEAR AS HEADLINES: what it came to, how it peaked, and how the    --}}
    {{-- ordinary month read                                                   --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Total Annual Income" theme="success" icon="fas fa-fw fa-money-bill-wave"
                :value="\App\Utility\MoneyHelper::format($report['year_total'])"
                :note="'Year ' . $report['year']" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Best Month" theme="primary" icon="fas fa-fw fa-award"
                :value="$report['best_month'] ? \App\Utility\PeriodHelper::shortLabel($report['best_month']['period']) : '—'"
                :note="$report['best_month']
                    ? \App\Utility\MoneyHelper::format($report['best_month']['amount']) . ' earned in it'
                    : 'Nothing posted in this year yet'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Monthly Average" theme="info" icon="fas fa-fw fa-chart-line"
                :value="\App\Utility\MoneyHelper::format($report['monthly_average'])"
                :note="$report['months_with_movement'] . ' of 12 months with income'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Income Accounts" theme="secondary" icon="fas fa-fw fa-list"
                :value="count($report['rows'])"
                note="Accounts that earned this year" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE TREND AND THE BREAKDOWN BEHIND IT                                --}}
    {{-- =================================================================== --}}
    @include('livewire.admin.reports.partials.monthly-breakdown', [
        'report' => $report,
        'heading' => 'Income by Account',
        'trendHeading' => 'Monthly Income Trend',
        'theme' => 'success',
    ])
</div>
