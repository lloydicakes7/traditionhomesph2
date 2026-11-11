<div>
    {{-- =================================================================== --}}
    {{-- THE YEAR'S SPENDING, READ THE SAME WAY THE INCOME REPORT IS          --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar subtitle="Annual expense breakdown by month and category">
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
    {{-- THE YEAR AS HEADLINES, read exactly as the income report's are        --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Total Annual Expenses" theme="danger" icon="fas fa-fw fa-file-invoice-dollar"
                :value="\App\Utility\MoneyHelper::format($report['year_total'])"
                :note="'Year ' . $report['year']" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Highest Month" theme="primary" icon="fas fa-fw fa-award"
                :value="$report['highest_month'] ? \App\Utility\PeriodHelper::shortLabel($report['highest_month']['period']) : '—'"
                :note="$report['highest_month']
                    ? \App\Utility\MoneyHelper::format($report['highest_month']['amount']) . ' spent in it'
                    : 'Nothing posted in this year yet'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Monthly Average" theme="info" icon="fas fa-fw fa-chart-line"
                :value="\App\Utility\MoneyHelper::format($report['monthly_average'])"
                :note="$report['months_with_movement'] . ' of 12 months with spending'" />
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <x-reports.kpi label="Expense Accounts" theme="secondary" icon="fas fa-fw fa-list"
                :value="count($report['rows'])"
                note="Accounts that took money this year" />
        </div>
    </div>

    @include('livewire.admin.reports.partials.monthly-breakdown', [
        'report' => $report,
        'heading' => 'Expenses by Account',
        'trendHeading' => 'Monthly Expense Trend',
        'theme' => 'danger',
    ])
</div>
