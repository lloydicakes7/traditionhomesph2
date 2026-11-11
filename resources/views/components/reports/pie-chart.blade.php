{{--
    A pie — or, by default, a donut — for a distribution that has to be read as
    shares of one whole.

    A gender split, a mix of civil statuses, a breakdown of where the bookings
    stand: each is a part-of-a-total question, which is exactly what a pie
    answers and what a bar cannot. The ring is a conic gradient built from the
    slices' shares, so the drawing costs no library and the legend beside it
    carries the same figures a tooltip would.

    The hole is not decoration: it is where the total is written, which is the
    one figure a pie of percentages otherwise hides.

    Each slice is ['label' => …, 'value' => …, 'theme' => …]; a slice that names
    no theme takes the palette's next colour, so neighbouring slices differ
    without the caller mapping them.

    Usage:
        <x-reports.pie-chart :data="$report['gender']" :center="$report['totals']['residents']" center-label="Residents" />
--}}
@props([
    'data'        => [],
    'donut'       => true,
    'money'       => false,
    'center'      => null,
    'centerLabel' => null,
])

@php
    $slices = collect($data)->values()->map(function ($slice, $index) {
        return [
            'label' => $slice['label'] ?? '',
            'value' => (float) ($slice['value'] ?? 0),
            'color' => \App\Utility\ChartHelper::color($slice['theme'] ?? \App\Utility\ChartHelper::PALETTE[$index % count(\App\Utility\ChartHelper::PALETTE)]),
        ];
    });

    $total = (float) $slices->sum('value');

    $stops = [];
    $cursor = 0.0;

    foreach ($slices as $slice) {
        $share = $total > 0 ? $slice['value'] / $total * 100 : 0;
        $stops[] = $slice['color'] . ' ' . round($cursor, 2) . '% ' . round($cursor + $share, 2) . '%';
        $cursor += $share;
    }

    $gradient = $stops
        ? 'conic-gradient(' . implode(', ', $stops) . ')'
        : 'conic-gradient(var(--border) 0% 100%)';

    $format = function ($value) use ($money) {
        return $money ? \App\Utility\MoneyHelper::format($value) : number_format($value);
    };
@endphp

@if ($slices->isEmpty() || $total <= 0)
    <x-reports.chart-empty />
@else
    <div class="report-pie-wrap">
        <div class="report-pie" style="background: {{ $gradient }};">
            @if ($donut && (filled($center) || filled($centerLabel)))
                <div class="report-pie-hole">
                    @if (filled($center))
                        <div class="report-pie-total">{{ $center }}</div>
                    @endif
                    @if (filled($centerLabel))
                        <div class="report-pie-total-label">{{ $centerLabel }}</div>
                    @endif
                </div>
            @endif
        </div>

        <ul class="report-legend list-unstyled mb-0">
            @foreach ($slices as $slice)
                <li class="report-legend-item">
                    <span class="report-legend-swatch" style="background: {{ $slice['color'] }};"></span>
                    <span class="report-legend-label" title="{{ $slice['label'] }}">{{ $slice['label'] }}</span>
                    <span class="report-legend-value amount-mono">{{ $format($slice['value']) }}</span>
                    <span class="report-legend-share">
                        {{ $total > 0 ? round($slice['value'] / $total * 100, 1) : 0 }}%
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
