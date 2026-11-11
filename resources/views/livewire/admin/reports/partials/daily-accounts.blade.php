{{--
    What a single day moved, account by account — the chart and the table the
    Daily Income and the Daily Expense Report both put under their own total.

    The two reports read the same day's movement and differ only in which side
    of the books they read (see LedgerReportQuery::movementByDay), so the
    drawing, the table and its cards live once.

    Variables:
        $report   — the service's reading: rows (account, amount) and total
        $heading  — what the chart and the table are (e.g. "Income by Account")
        $theme    — the AdminLTE theme the bars and the total are painted in
--}}
@php
    $bars = collect($report['rows'])->map(function ($row) {
        return [
            'label' => $row['account']->name,
            'value' => $row['amount'],
        ];
    })->all();

    $cards = collect($report['rows'])->map(function ($row) {
        return [
            'title'    => $row['account']->name,
            'subtitle' => $row['account']->code,
            'facts'    => [['label' => 'Amount', 'value' => \App\Utility\MoneyHelper::format($row['amount'])]],
        ];
    })->values()->all();

    if (! empty($report['rows'])) {
        $cards[] = [
            'title' => 'Total',
            'facts' => [['label' => 'Amount', 'value' => \App\Utility\MoneyHelper::format($report['total']), 'strong' => true, 'theme' => $theme]],
        ];
    }
@endphp

<div class="card">
    <div class="card-header">
        <span class="font-weight-bold"><i class="fas fa-fw fa-list mr-1"></i> {{ $heading }}</span>
    </div>

    @if (! empty($report['rows']))
        <div class="card-body pb-2">
            <x-reports.bar-chart :data="$bars" orientation="horizontal" :theme="$theme" money />
        </div>
    @endif

    <div class="card-body p-0 report-table-view">
        <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 14%;">Code</th>
                    <th>Account</th>
                    <th class="text-right" style="width: 26%;">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td class="align-middle">{{ $row['account']->code }}</td>
                        <td class="align-middle text-break">{{ $row['account']->name }}</td>
                        <td class="text-right amount-mono align-middle">
                            {{ \App\Utility\MoneyHelper::format($row['amount']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="fas fa-fw fa-calendar-day fa-2x mb-2"></i>
                            <p class="mb-0">Nothing was posted on this date — pick another day.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if (! empty($report['rows']))
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td class="text-right amount-mono text-{{ $theme }}">
                            {{ \App\Utility\MoneyHelper::format($report['total']) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <x-reports.cards class="card-body" :rows="$cards"
        empty="Nothing was posted on this date — pick another day." />
</div>
