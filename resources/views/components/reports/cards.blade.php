{{--
    A report's records as cards — what the report's table becomes on a screen
    too narrow to hold it.

    Every report table is two to fifteen columns of figures, so below `xl` the
    table stands down and one card per record stands up: the record's own name,
    then its column labels beside their figures (see the `.report-table-view` /
    `.report-card-view` rules in reports.css). A report writes its table once and
    its rows once, and the stylesheet decides which of the two the reader sees —
    nothing in the suite wraps a table in a `.table-responsive` any more, so no
    report scrolls sideways on a phone or on paper.

    The label→figure reading is the whole point of the card: a fact is one cell
    of the row the table prints. A fact may be written as a badge instead of a
    figure (a status, an age band), in a theme colour, or bold (a subtotal).

    Each row is:
        'group'    => a heading printed above the card when it changes (optional)
        'title'    => the record's own name — the row's first column
        'subtitle' => a second identifying line (optional)
        'facts'    => [['label' => …, 'value' => …, 'theme' => 'success',
                        'strong' => true, 'badge' => 'warning'], …]
        'total'    => ['label' => 'Total', 'value' => …] — the footer figure

    Usage:
        <x-reports.cards :rows="$cards" empty="Nothing was posted in this period." />
--}}
@props([
    'rows'  => [],
    'empty' => 'Nothing to show for this period.',
    'icon'  => 'fas fa-fw fa-folder-open',
])

<div {{ $attributes->merge(['class' => 'report-card-view']) }}>
    @php $heading = null; @endphp

    @forelse ($rows as $row)
        @if (filled($row['group'] ?? null) && $row['group'] !== $heading)
            @php $heading = $row['group']; @endphp
            <div class="report-card-group">{{ $heading }}</div>
        @endif

        <div class="card report-card">
            @if (filled($row['title'] ?? null))
                <div class="report-card-head">
                    <div class="report-card-title">{{ $row['title'] }}</div>

                    @if (filled($row['subtitle'] ?? null))
                        <div class="report-card-subtitle">{{ $row['subtitle'] }}</div>
                    @endif
                </div>
            @endif

            @if (! empty($row['facts']))
                <dl class="report-card-body report-card-facts">
                    @foreach ($row['facts'] as $fact)
                        <div class="report-card-fact">
                            <dt>{{ $fact['label'] }}</dt>
                            <dd>
                                @if (filled($fact['badge'] ?? null))
                                    <span class="badge badge-{{ $fact['badge'] }}">{{ $fact['value'] }}</span>
                                @else
                                    <span class="{{ ($fact['strong'] ?? false) ? 'font-weight-bold ' : '' }}{{ filled($fact['theme'] ?? null) ? 'text-' . $fact['theme'] : '' }}">{{ $fact['value'] }}</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @if (! empty($row['total']))
                <div class="report-card-total">
                    <span>{{ $row['total']['label'] }}</span>
                    <span class="amount-mono font-weight-bold">{{ $row['total']['value'] }}</span>
                </div>
            @endif
        </div>
    @empty
        <div class="card report-card">
            <div class="report-card-empty">
                <i class="{{ $icon }} fa-2x mb-2"></i>
                <p class="mb-0">{{ $empty }}</p>
            </div>
        </div>
    @endforelse
</div>
