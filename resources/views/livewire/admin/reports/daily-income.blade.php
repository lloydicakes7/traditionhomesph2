<div>
    {{-- =================================================================== --}}
    {{-- THE DAY, AND WHAT IT EARNED                                          --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar :subtitle="'Posted on ' . \Carbon\Carbon::parse($report['date'])->format('F j, Y')">
        <x-slot name="filters">
            <x-adminlte-input name="date" type="date" wire:model="date" igroup-size="sm" fgroup-class="mb-0" />
            <span wire:loading wire:target="date" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <x-reports.kpi label="Total Income" theme="success" icon="fas fa-fw fa-arrow-down"
                :value="\App\Utility\MoneyHelper::format($report['total'])"
                :note="'Date: ' . \Carbon\Carbon::parse($report['date'])->format('F j, Y')" />
        </div>
        <div class="col-12 col-md-6 mb-3">
            <x-reports.kpi label="Income Accounts" icon="fas fa-fw fa-list"
                :value="count($report['rows'])" theme="secondary" />
        </div>
    </div>

    @include('livewire.admin.reports.partials.daily-accounts', [
        'report' => $report,
        'heading' => 'Income by Account',
        'theme' => 'success',
    ])
</div>
