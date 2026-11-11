{{--
    A line chart for a figure that moves over time — a month-by-month trend, a
    day-on-day movement — where a bar chart would ask the reader to compare
    heights instead of read a shape.

    It is inline SVG rather than a charting library: the panel ships none, and a
    polyline through the period's figures is all the drawing a trend needs. The
    categories (months, days) ride beneath in a row that stays crisp at any
    width, and an area fill under the line makes the direction readable even in
    a screenshot.

    One line is the common case, passed as `data`/`theme`. A comparison — income
    against expenses — is passed as `series`, a list of
    ['label' => …, 'theme' => …, 'data' => …]: the lines then share one scale
    (their common peak) so reading them against each other is honest, a legend
    names each of them, and the area fill is dropped because overlapping fills
    muddle the shape they are meant to clarify.

    Each point is ['label' => …, 'value' => …].

    Usage:
        <x-reports.line-chart :data="$months" theme="success" money />

        <x-reports.line-chart money :series="[
            ['label' => 'Income',   'theme' => 'success', 'data' => $income],
            ['label' => 'Expenses', 'theme' => 'danger',  'data' => $expenses],
        ]" />
--}}
@props([
    'data'   => [],
    'series' => [],
    'theme'  => 'primary',
    'money'  => false,
    'area'   => true,
])

@php
    // One shape for the drawing, whichever way the caller passed the lines in.
    $lines = collect($series ?: [['label' => null, 'theme' => $theme, 'data' => $data]])
        ->map(function ($line) {
            return [
                'label'  => $line['label'] ?? null,
                'color'  => \App\Utility\ChartHelper::color($line['theme'] ?? 'primary'),
                'points' => collect($line['data'] ?? [])->values(),
            ];
        })
        ->values();

    $multi = $lines->count() > 1;

    $count = (int) $lines->max(function ($line) {
        return $line['points']->count();
    });

    // One scale for every line, read off their common peak.
    $peak = (float) $lines->flatMap(function ($line) {
        return $line['points']->pluck('value');
    })->map(function ($value) {
        return (float) $value;
    })->max();

    $labels = $lines->first()['points']->pluck('label');

    $width = 1000;
    $height = 300;
    $topPadding = 20;
    $bottomPadding = 20;
    $plotHeight = $height - $topPadding - $bottomPadding;

    // Each line as the coordinates the SVG draws: one (x, y) per point, x spread
    // evenly across the width, y scaled to the shared peak.
    $drawing = [];

    foreach ($lines as $line) {
        $coords = [];

        foreach ($line['points'] as $index => $point) {
            $value = (float) ($point['value'] ?? 0);
            $ratio = $peak > 0 ? $value / $peak : 0;

            $coords[] = [
                'x'     => $count > 1 ? round($index / ($count - 1) * $width, 2) : $width / 2,
                'y'     => round($height - $bottomPadding - $ratio * $plotHeight, 2),
                'label' => $point['label'] ?? '',
                'value' => $value,
            ];
        }

        $drawing[] = [
            'label'  => $line['label'],
            'color'  => $line['color'],
            'coords' => $coords,
            'path'   => implode(' ', array_map(function ($coord) {
                return $coord['x'] . ',' . $coord['y'];
            }, $coords)),
        ];
    }
@endphp

@if ($count === 0)
    <x-reports.chart-empty />
@else
    <div class="report-line-wrap">
        @if ($multi)
            <div class="report-legend report-legend-inline">
                @foreach ($drawing as $line)
                    <div class="report-legend-item">
                        <span class="report-legend-swatch" style="background: {{ $line['color'] }};"></span>
                        <span class="report-legend-label">{{ $line['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <svg class="report-line" viewBox="0 0 {{ $width }} {{ $height }}"
            preserveAspectRatio="xMidYMid meet" role="img">

            @foreach ([0.25, 0.5, 0.75] as $guide)
                <line x1="0" y1="{{ $height - $bottomPadding - $guide * $plotHeight }}"
                    x2="{{ $width }}" y2="{{ $height - $bottomPadding - $guide * $plotHeight }}"
                    class="report-line-grid" />
            @endforeach

            <line x1="0" y1="{{ $height - $bottomPadding }}"
                x2="{{ $width }}" y2="{{ $height - $bottomPadding }}" class="report-line-axis" />

            @foreach ($drawing as $line)
                {{-- An area fill only reads clearly under a single line. --}}
                @if ($area && ! $multi && count($line['coords']) > 1)
                    <path class="report-line-area" style="fill: {{ $line['color'] }};"
                        d="M0,{{ $height }} L{{ str_replace(' ', ' L', $line['path']) }} L{{ $width }},{{ $height }} Z" />
                @endif

                @if (count($line['coords']) > 1)
                    <polyline points="{{ $line['path'] }}" class="report-line-path"
                        style="stroke: {{ $line['color'] }};" />
                @endif

                @foreach ($line['coords'] as $coord)
                    <circle cx="{{ $coord['x'] }}" cy="{{ $coord['y'] }}" r="5"
                        class="report-line-dot" style="fill: {{ $line['color'] }};">
                        <title>{{ $coord['label'] }}: {{ $money ? \App\Utility\MoneyHelper::format($coord['value']) : number_format($coord['value']) }}</title>
                    </circle>
                @endforeach
            @endforeach
        </svg>

        <div class="report-line-labels">
            @foreach ($labels as $label)
                <div class="report-line-label">{{ $label }}</div>
            @endforeach
        </div>
    </div>
@endif
