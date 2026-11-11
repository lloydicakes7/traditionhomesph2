{{--
    A box plot — the spread of a set of figures, not just its average.

    Age is the figure this exists for: "the average resident is 41" hides
    whether the association is one age or every age, while the box says where
    the middle half sits and how far the whiskers run. Each series is drawn on a
    shared scale, so two boxes can be compared by eye, and every one carries its
    own min–max note for the reader who wants the numbers.

    Each series is ['label' => …, 'min' => …, 'q1' => …, 'median' => …, 'q3' => …,
    'max' => …, 'theme' => …].

    Usage:
        <x-reports.box-plot :data="$report['age_by_sex']" unit="yrs" />
--}}
@props([
    'data' => [],
    'unit' => null,
])

@php
    $series = collect($data)->values()->map(function ($row) {
        return [
            'label'  => $row['label'] ?? '',
            'min'    => (float) ($row['min'] ?? 0),
            'q1'     => (float) ($row['q1'] ?? 0),
            'median' => (float) ($row['median'] ?? 0),
            'q3'     => (float) ($row['q3'] ?? 0),
            'max'    => (float) ($row['max'] ?? 0),
            'color'  => \App\Utility\ChartHelper::color($row['theme'] ?? null),
        ];
    });

    $lo = $series->min('min');
    $hi = $series->max('max');

    if ($lo === null || $hi === null) {
        $lo = 0;
        $hi = 0;
    }

    $range = ($hi - $lo) > 0 ? ($hi - $lo) : 1;

    $position = function ($value) use ($lo, $range) {
        return round(min(max(($value - $lo) / $range * 100, 0), 100), 2);
    };
@endphp

@if ($series->isEmpty())
    <x-reports.chart-empty />
@else
    <div class="report-boxplots">
        @foreach ($series as $row)
            <div class="report-boxplot-row">
                <div class="report-boxplot-label">{{ $row['label'] }}</div>

                <div class="report-boxplot-track">
                    <div class="report-boxplot-whisker"
                        style="left: {{ $position($row['min']) }}%; width: {{ $position($row['max']) - $position($row['min']) }}%;"></div>

                    <div class="report-boxplot-box"
                        style="left: {{ $position($row['q1']) }}%; width: {{ max($position($row['q3']) - $position($row['q1']), 0.5) }}%; background: {{ $row['color'] }};"></div>

                    <div class="report-boxplot-median"
                        style="left: {{ $position($row['median']) }}%;"></div>

                    <div class="report-boxplot-cap" style="left: {{ $position($row['min']) }}%;"></div>
                    <div class="report-boxplot-cap" style="left: {{ $position($row['max']) }}%;"></div>
                </div>

                <div class="report-boxplot-note">
                    {{ round($row['min']) }}–{{ round($row['max']) }}{{ $unit ? ' ' . $unit : '' }}
                </div>
            </div>
        @endforeach

        <div class="report-boxplot-row report-boxplot-axis">
            <div class="report-boxplot-label"></div>
            <div class="report-boxplot-track d-flex justify-content-between">
                <span>{{ round($lo) }}</span>
                <span>{{ round($hi) }}</span>
            </div>
            <div class="report-boxplot-note"></div>
        </div>
    </div>
@endif
