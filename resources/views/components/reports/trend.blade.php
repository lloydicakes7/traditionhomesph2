{{--
    A month-by-month trend, drawn as the generic bar chart.

    A trend is a bar chart whose categories happen to be months; this component
    exists so the monthly reports can say "trend" and keep their old calling
    shape, while the drawing itself lives once, in bar-chart.

    Usage:
        <x-reports.trend :bars="$report['months']" :labels="$report['month_labels']" theme="danger" />
--}}
@props([
    'bars'   => [],
    'labels' => [],
    'theme'  => 'primary',
    'money'  => true,
])

@php
    $data = [];

    foreach ($bars as $key => $amount) {
        $data[] = [
            'label' => $labels[$key] ?? $key,
            'value' => $amount,
        ];
    }
@endphp

<x-reports.bar-chart :data="$data" :theme="$theme" :money="$money" />
