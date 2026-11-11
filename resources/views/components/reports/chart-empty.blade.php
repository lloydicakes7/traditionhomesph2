{{--
    What a chart shows when it has nothing to draw — a distribution with no
    records behind it, or a filter that matched none. The message can say which
    case it is, so the reader is not left guessing whether the data or the
    filter is empty.

    Usage:
        <x-reports.chart-empty message="No resident is on file yet." />
--}}
@props([
    'message' => 'Nothing to chart yet.',
])

<div class="report-chart-empty text-center text-muted py-4">
    <i class="fas fa-fw fa-chart-area fa-2x mb-2 d-block"></i>
    <span class="small">{{ $message }}</span>
</div>
