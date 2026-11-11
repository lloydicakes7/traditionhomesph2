{{--
    One figure inside a report band — a label, its value, and an optional note,
    painted on top of the band rather than in a colour of its own.

    Usage:
        <x-reports.band-tile label="Total Assets" :value="\App\Utility\MoneyHelper::format($total)" />
--}}
@props([
    'label' => '',
    'value' => '',
    'note'  => null,
])

<div class="report-band-tile">
    <div class="report-band-label">{{ $label }}</div>
    <div class="report-band-value">{{ $value }}</div>
    @if (filled($note))
        <div class="report-kpi-note">{{ $note }}</div>
    @endif
</div>
