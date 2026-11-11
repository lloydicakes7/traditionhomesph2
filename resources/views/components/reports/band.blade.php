{{--
    The band a statement opens with — the financial position at a glance: the
    heading, and the two or three figures the statement is about, on the
    association's own colour.

    Usage:
        <x-reports.band title="Financial Position">
            <div class="col-12 col-md-4">
                <x-reports.band-tile label="Total Assets" :value="…" />
            </div>
        </x-reports.band>
--}}
@props([
    'title' => 'Financial Position',
])

<div class="report-band">
    <h3 class="text-center mb-3">{{ $title }}</h3>

    <div class="row">
        {{ $slot }}
    </div>
</div>
