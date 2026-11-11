{{--
    A bubble chart — three figures per category at once, where a bar chart can
    only carry two.

    An amenity's bookings, how many of them are still ahead, and how many were
    taken this year are one story told three ways; the bubble puts them side by
    side, so the amenity that is busy and the amenity that is booked out are
    told apart rather than averaged together. The horizontal axis is the first
    figure, the vertical the second, and the area of the circle the third, so a
    bigger bubble always means more of whatever it stands for.

    Each point is ['x' => …, 'y' => …, 'r' => …, 'label' => …, 'note' => …,
    'theme' => …]; a point that names no theme takes the palette's next colour.

    Usage:
        <x-reports.bubble-chart :data="$report['bubbles']"
            x-label="Bookings" y-label="Upcoming" />
--}}
@props([
    'data'   => [],
    'xLabel' => null,
    'yLabel' => null,
])

@php
    $points = collect($data)->values()->map(function ($point, $index) {
        return [
            'x'     => (float) ($point['x'] ?? 0),
            'y'     => (float) ($point['y'] ?? 0),
            'r'     => (float) ($point['r'] ?? 0),
            'label' => $point['label'] ?? '',
            'note'  => $point['note'] ?? null,
            'color' => \App\Utility\ChartHelper::color(
                $point['theme'] ?? \App\Utility\ChartHelper::PALETTE[$index % count(\App\Utility\ChartHelper::PALETTE)]
            ),
        ];
    });

    $width = 1000;
    $height = 460;
    $padLeft = 70;
    $padRight = 30;
    $padTop = 30;
    $padBottom = 46;

    $plotWidth = $width - $padLeft - $padRight;
    $plotHeight = $height - $padTop - $padBottom;

    $xMin = (float) ($points->min('x') ?? 0);
    $xMax = (float) ($points->max('x') ?? 0);
    $yMin = (float) ($points->min('y') ?? 0);
    $yMax = (float) ($points->max('y') ?? 0);
    $rMax = (float) ($points->max('r') ?? 0);

    $xRange = ($xMax - $xMin) > 0 ? ($xMax - $xMin) : 1;
    $yRange = ($yMax - $yMin) > 0 ? ($yMax - $yMin) : 1;

    $scaleX = function ($value) use ($padLeft, $plotWidth, $xMin, $xRange) {
        return round($padLeft + ($value - $xMin) / $xRange * $plotWidth, 2);
    };

    $scaleY = function ($value) use ($height, $padBottom, $plotHeight, $yMin, $yRange) {
        return round($height - $padBottom - ($value - $yMin) / $yRange * $plotHeight, 2);
    };

    $radius = function ($value) use ($rMax) {
        return round(7 + sqrt($rMax > 0 ? $value / $rMax : 0) * 30, 2);
    };
@endphp

@if ($points->isEmpty())
    <x-reports.chart-empty />
@else
    <div class="report-bubble-wrap">
        <svg class="report-bubble" viewBox="0 0 {{ $width }} {{ $height }}"
            preserveAspectRatio="xMidYMid meet" role="img">

            @foreach ([0.25, 0.5, 0.75] as $line)
                <line x1="{{ $padLeft }}" y1="{{ $padTop + $line * $plotHeight }}"
                    x2="{{ $width - $padRight }}" y2="{{ $padTop + $line * $plotHeight }}"
                    class="report-line-grid" />
                <line x1="{{ $padLeft + $line * $plotWidth }}" y1="{{ $padTop }}"
                    x2="{{ $padLeft + $line * $plotWidth }}" y2="{{ $height - $padBottom }}"
                    class="report-line-grid" />
            @endforeach

            <line x1="{{ $padLeft }}" y1="{{ $height - $padBottom }}"
                x2="{{ $width - $padRight }}" y2="{{ $height - $padBottom }}" class="report-line-axis" />
            <line x1="{{ $padLeft }}" y1="{{ $padTop }}"
                x2="{{ $padLeft }}" y2="{{ $height - $padBottom }}" class="report-line-axis" />

            @foreach ($points as $point)
                <circle cx="{{ $scaleX($point['x']) }}" cy="{{ $scaleY($point['y']) }}"
                    r="{{ $radius($point['r']) }}" class="report-bubble-dot"
                    style="fill: {{ $point['color'] }};">
                    <title>{{ $point['label'] }}{{ $point['note'] ? ' — ' . $point['note'] : '' }}</title>
                </circle>
            @endforeach

            <text x="{{ $padLeft }}" y="{{ $height - 14 }}" class="report-bubble-tick" text-anchor="start">
                {{ $xLabel ? $xLabel . ': ' : '' }}{{ round($xMin) }}
            </text>
            <text x="{{ $width - $padRight }}" y="{{ $height - 14 }}" class="report-bubble-tick" text-anchor="end">
                {{ round($xMax) }}
            </text>
            <text x="{{ $padLeft - 10 }}" y="{{ $padTop + 4 }}" class="report-bubble-tick" text-anchor="end">
                {{ round($yMax) }}
            </text>
            <text x="{{ $padLeft - 10 }}" y="{{ $height - $padBottom }}" class="report-bubble-tick" text-anchor="end">
                {{ $yLabel ? $yLabel . ': ' : '' }}{{ round($yMin) }}
            </text>
        </svg>

        <ul class="report-legend report-legend-inline list-unstyled mb-0">
            @foreach ($points as $point)
                <li class="report-legend-item">
                    <span class="report-legend-swatch" style="background: {{ $point['color'] }};"></span>
                    <span class="report-legend-label" title="{{ $point['label'] }}">{{ $point['label'] }}</span>
                    @if ($point['note'])
                        <span class="report-legend-share">{{ $point['note'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
