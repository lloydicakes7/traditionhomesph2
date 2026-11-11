{{--
    A year's movement, drawn and tabulated — the shape the Monthly Income and
    the Monthly Expense Report both take: the trend beside a line of the same
    movement, the account-by-month breakdown below, and the twelve month totals
    under it.

    The two reports read the same roll-up and differ only in what it is about
    (see LedgerReportQuery::movementByMonth), so the drawing of it lives once
    and each page names its own heading, colour and money.

    Two drawings of one series, because they answer different questions: the
    bars compare the months to each other, the line shows the shape of the year.

    The breakdown is fifteen columns wide — a year of months beside a code, an
    account and a total — so it takes the suite's smallest-screen shape (see
    `.report-table-view` / `.report-card-view` in reports.css): a fixed table on
    an extra-large screen, and one card per account below it. Nothing is put in
    a `.table-responsive`, so the report never scrolls sideways. Its card also
    carries `report-prints-cards`, because fifteen columns do not fit a sheet
    either — on paper this report is its cards.

    Variables:
        $report       — the service's reading: rows, months, month_labels, year_total
        $heading      — what the table is (e.g. "Income by Account")
        $trendHeading — what the chart is (e.g. "Monthly Income Trend")
        $theme        — the AdminLTE theme the bars are painted in
--}}
@php
    $columns = count($report['month_labels']);

    $series = [];

    foreach ($report['months'] as $month => $amount) {
        $series[] = [
            'label' => $report['month_labels'][$month] ?? $month,
            'value' => $amount,
        ];
    }
@endphp

<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="font-weight-bold">
                    <i class="fas fa-fw fa-chart-bar mr-1"></i> {{ $trendHeading }}
                </span>
            </div>
            <div class="card-body">
                <x-reports.trend :bars="$report['months']" :labels="$report['month_labels']" :theme="$theme" />
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="font-weight-bold">
                    <i class="fas fa-fw fa-chart-line mr-1"></i> {{ $trendHeading }} — Movement
                </span>
            </div>
            <div class="card-body">
                <x-reports.line-chart :data="$series" :theme="$theme" money />
            </div>
        </div>
    </div>
</div>

<div class="card report-prints-cards">
    <div class="card-header">
        <span class="font-weight-bold"><i class="fas fa-fw fa-table mr-1"></i> {{ $heading }}</span>
    </div>

    {{-- =================================================================== --}}
    {{-- A TABLE ON A WIDE SCREEN                                            --}}
    {{-- =================================================================== --}}
    <div class="card-body p-0 report-table-view">
        <table class="table table-sm table-hover mb-0 w-100 small">
            <thead class="bg-light">
                <tr>
                    <th class="px-1">Code</th>
                    <th class="px-1">Account</th>
                    @foreach ($report['month_labels'] as $label)
                        <th class="px-1">{{ $label }}</th>
                    @endforeach
                    <th class="px-1">Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td class="px-1">{{ $row['account']->code }}</td>
                        <td class="px-1 text-break">{{ $row['account']->name }}</td>
                        @foreach ($row['months'] as $amount)
                            <td class="px-1 amount-mono">
                                {{ round((float) $amount, 2) != 0.0 ? \App\Utility\MoneyHelper::format($amount) : '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 amount-mono font-weight-bold">
                            {{ \App\Utility\MoneyHelper::format($row['total']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columns + 3 }}" class="text-center text-muted py-4">
                            <i class="fas fa-fw fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">Nothing was posted in this year — pick another one.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if (! empty($report['rows']))
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="2" class="px-1 text-right">TOTAL</th>
                        @foreach ($report['months'] as $amount)
                            <th class="px-1 amount-mono">{{ \App\Utility\MoneyHelper::format($amount) }}</th>
                        @endforeach
                        <th class="px-1 amount-mono">{{ \App\Utility\MoneyHelper::format($report['year_total']) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- =================================================================== --}}
    {{-- ONE CARD PER ACCOUNT ON A SMALL SCREEN — and on paper, where the      --}}
    {{-- twelve months cannot fit a sheet                                      --}}
    {{-- =================================================================== --}}
    <div class="card-body report-card-view">
        @forelse ($report['rows'] as $row)
            <div class="card report-card">
                <div class="report-card-head">
                    <div class="report-card-title">{{ $row['account']->name }}</div>
                    <div class="report-card-subtitle">{{ $row['account']->code }}</div>
                </div>

                <div class="report-card-body">
                    <div class="d-flex flex-wrap" style="gap:0.4rem;">
                        @foreach ($row['months'] as $month => $amount)
                            <div class="border rounded px-2 py-1 text-center" style="min-width:64px;">
                                <div class="text-muted" style="font-size:0.65rem;">
                                    {{ $report['month_labels'][$month] ?? $month }}
                                </div>
                                <div class="small amount-mono">
                                    {{ round((float) $amount, 2) != 0.0 ? \App\Utility\MoneyHelper::format($amount) : '—' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="report-card-total">
                    <span>Total</span>
                    <span class="amount-mono font-weight-bold">
                        {{ \App\Utility\MoneyHelper::format($row['total']) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="card report-card">
                <div class="report-card-empty">
                    <i class="fas fa-fw fa-folder-open fa-2x mb-2"></i>
                    <p class="mb-0">Nothing was posted in this year — pick another one.</p>
                </div>
            </div>
        @endforelse

        @if (! empty($report['rows']))
            <div class="card report-card">
                <div class="report-card-total">
                    <span>Total for the year</span>
                    <span class="amount-mono font-weight-bold">
                        {{ \App\Utility\MoneyHelper::format($report['year_total']) }}
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>
