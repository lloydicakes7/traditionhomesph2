{{--
    A report's header and its filters — two cards, on purpose.

    The first card is the report's name: what it covers on the left, and the
    controls that send it out of the system on the right. It prints, because it
    says what the page is.

    The filters are their own card, and are never printed — a date range is how
    you read a report, not part of it. A report with no filters of its own
    passes no slot and gets no card.

    Print hands off to the browser (the stylesheet drops the panel's chrome, the
    filter card and every button, and keeps the report itself) while Export asks
    the Livewire component behind the report to stream its CSV, which is the
    only way a download can leave a Livewire page.

    Usage:
        <x-reports.toolbar subtitle="As of June 30, 2026">
            <x-slot name="filters">…date input, account select…</x-slot>
        </x-reports.toolbar>
--}}
@props([
    'subtitle'   => null,
    'exportable' => true,
])

<div class="card report-toolbar">
    <div class="py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="text-muted small">
            @if (filled($subtitle))
                <i class="fas fa-fw fa-info-circle mr-1"></i>{{ $subtitle }}
            @endif
        </div>

        <div class="report-toolbar-controls">
            <x-adminlte-button label="Print" theme="secondary" icon="fas fa-fw fa-print"
                onclick="window.print()" class="btn-sm" />

            @if ($exportable)
                @can('report.export')
                    <x-adminlte-button label="Export CSV" theme="success" icon="fas fa-fw fa-file-export"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting the report…" class="btn-sm" />
                @endcan
            @endif
        </div>
    </div>
</div>

@if (trim($filters ?? '') !== '')
    <div class="card report-filters no-print">
        <div class="py-3 px-3">
            <div class="report-filters-row">
                {{ $filters }}
            </div>
        </div>
    </div>
@endif
