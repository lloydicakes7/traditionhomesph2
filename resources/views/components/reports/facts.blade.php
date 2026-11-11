{{--
    A report's figures as one list of labels and values — the shape a report
    table of two columns takes on a screen too small for it.

    A record list needs a card each (x-reports.cards); a two-column list — the
    association's records, the residents report in numbers — is one column of
    labels beside one column of figures, which is a list rather than a stack of
    records. It reads the same as the table it replaces, without the table.

    Each row is ['label' => …, 'value' => …, 'theme' => 'danger', 'badge' => 'warning'].

    Usage:
        <x-reports.facts :rows="$report['records']" />
--}}
@props([
    'rows'  => [],
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'card report-card report-card-view']) }}>
    @if (filled($title))
        <div class="report-card-head">
            <div class="report-card-title">{{ $title }}</div>
        </div>
    @endif

    <dl class="report-card-body report-card-facts mb-0">
        @foreach ($rows as $row)
            <div class="report-card-fact">
                <dt>{{ $row['label'] }}</dt>
                <dd>
                    @if (filled($row['badge'] ?? null))
                        <span class="badge badge-{{ $row['badge'] }}">{{ $row['value'] }}</span>
                    @else
                        <span class="{{ ($row['strong'] ?? false) ? 'font-weight-bold ' : '' }}{{ filled($row['theme'] ?? null) ? 'text-' . $row['theme'] : '' }}">{{ $row['value'] }}</span>
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
</div>
