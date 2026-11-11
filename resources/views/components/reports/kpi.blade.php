{{--
    Usage:
        <x-reports.kpi theme="success" label="Total Collections"
            icon="fas fa-hand-holding-usd"
            :value="\App\Utility\MoneyHelper::format($report['total'])" note="June 3, 2026" />
--}}
@props([
    'label'  => '',
    'value'  => '',
    'note'   => null,
    'icon'   => null,
    'theme'  => 'primary',
])

<div {{ $attributes->merge(['class' => "card report-kpi bg-{$theme} text-white"]) }}>
    <div class="report-kpi-inner">
        <div class="report-kpi-value">{{ $value }}</div>
        <div class="report-kpi-label">{{ $label }}</div>

        @if (filled($note))
            <div class="report-kpi-note">{{ $note }}</div>
        @endif
    </div>

    @if ($icon)
        <div class="report-kpi-icon">
            <i class="{{ $icon }}"></i>
        </div>
    @endif
</div>
