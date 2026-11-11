{{--
    A bar chart, vertical or horizontal — the report suite's way of comparing a
    figure across categories.

    It is plain markup rather than a charting library: each bar's length is its
    share of the largest figure, which is all a comparison of a dozen categories
    ever needs, and it keeps the panel free of a library it does not ship.

    The orientation is the reader's choice: months and age bands read left to
    right as columns, while a list of religions or expense categories reads
    better as rows with the label beside its bar.

    Each row is ['label' => …, 'value' => …, 'theme' => …]; a row that names no
    theme takes the chart's own, or — when `cycle` is set — the palette's next
    colour, which is how a categorical series (aging buckets, methods) reads as
    one series of distinct parts. `money` decides whether the figure is written
    as the association writes an amount or as a plain count.

    Usage:
        <x-reports.bar-chart :data="$rows" orientation="horizontal" money />
--}}
@props([
    'data'        => [],
    'orientation' => 'vertical',
    'theme'       => 'primary',
    'money'       => false,
    'height'      => null,
    'cycle'       => false,
])

@php
    $palette = \App\Utility\ChartHelper::PALETTE;

    $rows = collect($data)->map(function ($row, $index) use ($theme, $cycle, $palette) {
        $fallback = $cycle ? $palette[$index % count($palette)] : $theme;

        return [
            'label' => $row['label'] ?? '',
            'value' => (float) ($row['value'] ?? 0),
            'color' => \App\Utility\ChartHelper::color($row['theme'] ?? $fallback),
        ];
    })->values();

    $peak = (float) ($rows->max('value') ?: 0);

    $format = function ($value) use ($money) {
        return $money
            ? \App\Utility\MoneyHelper::formatShort($value)
            : number_format($value);
    };
@endphp

@if ($rows->isEmpty())
    <x-reports.chart-empty />
@elseif ($orientation === 'horizontal')
    <div class="report-bars report-bars-horizontal">
        @foreach ($rows as $row)
            @php $width = $peak > 0 ? max(round($row['value'] / $peak * 100, 2), 0.5) : 0.5; @endphp
            <div class="report-bar-row">
                <div class="report-bar-label" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                <div class="report-bar-track">
                    <div class="report-bar" style="width: {{ $width }}%; background: {{ $row['color'] }};"></div>
                </div>
                <div class="report-bar-amount amount-mono">{{ $format($row['value']) }}</div>
            </div>
        @endforeach
    </div>
@else
    <div class="report-bars report-bars-vertical" @if ($height) style="height: {{ $height }};" @endif>
        @foreach ($rows as $row)
            @php $barHeight = $peak > 0 ? max(round($row['value'] / $peak * 100, 2), 0.5) : 0.5; @endphp
            <div class="report-bar-col">
                <div class="report-bar-amount amount-mono">{{ $format($row['value']) }}</div>
                <div class="report-bar-track">
                    <div class="report-bar" style="height: {{ $barHeight }}%; background: {{ $row['color'] }};"></div>
                </div>
                <div class="report-bar-label">{{ $row['label'] }}</div>
            </div>
        @endforeach
    </div>
@endif
