@extends('pdf.layouts.report')
{{--
    FINANCIAL REPORT — what the association earned and spent over a span of its
    books, and the cash it held at the end of it.
    Builder: FinancialReportBuilder.

    The sheet is read top to bottom: income, expenses, what the two come to, and
    then the cash position — the money in total, what of it is in the bank, and
    what is left in the cash box.

    Its figures are set in the plain table (`.tbl.plain`): the accounts and their
    amounts line up as one statement, the way the association's own report reads,
    rather than as a boxed grid.

    @var array          $period    [from, to, label, heading]
    @var array          $income    [rows => [[code, account, amount], ...], total]
    @var array          $expenses  the same shape
    @var array          $net       [amount, note]
    @var array          $cash      [rows => [[code, account, where, amount], ...], total, in_bank, on_hand]
    @var array          $letterhead  (signatories.treasurer, signatories.president)
    @var \Carbon\Carbon $printedAt @var string $generatedBy
--}}
@section('title', 'Financial Report — ' . $period['label'])
@section('running_header', 'Financial Report · ' . $period['label'])

@section('content')
    <h2 class="report-title">Financial Report</h2>
    <p class="report-subtitle">{{ $period['heading'] }}</p>

    {{-- ============================================================= --}}
    {{-- WHAT THE ASSOCIATION EARNED AND SPENT                          --}}
    {{-- ============================================================= --}}
    <div class="sect">Income</div>
    <table class="tbl plain">
        <thead>
            <tr>
                <th>Account</th>
                <th class="amount" style="width:26%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($income['rows'] as $row)
                <tr>
                    <td>{{ $row['account'] }}</td>
                    <td class="amount">{{ $row['amount'] }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="center muted">No income account is on the chart.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="right">TOTAL INCOME</td>
                <td class="amount">{{ $income['total'] }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="sect">Expenses</div>
    <table class="tbl plain">
        <thead>
            <tr>
                <th>Account</th>
                <th class="amount" style="width:26%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses['rows'] as $row)
                <tr>
                    <td>{{ $row['account'] }}</td>
                    <td class="amount">{{ $row['amount'] }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="center muted">No expense account is on the chart.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="right">TOTAL EXPENSES</td>
                <td class="amount">{{ $expenses['total'] }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="totals">
        <tr class="grand">
            <td class="k">TOTAL INCOME LESS TOTAL EXPENSES</td>
            <td class="v">{{ $net['amount'] }}</td>
        </tr>
    </table>

    {{-- ============================================================= --}}
    {{-- WHAT THE ASSOCIATION HELD AT THE END OF THE SPAN               --}}
    {{-- ============================================================= --}}
    <div class="sect">Cash at the end of the span</div>
    <table class="tbl plain">
        <thead>
            <tr>
                <th>Account</th>
                <th style="width:16%;">Where</th>
                <th class="amount" style="width:22%;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cash['rows'] as $row)
                <tr>
                    <td>{{ $row['account'] }}</td>
                    <td>{{ $row['where'] }}</td>
                    <td class="amount">{{ $row['amount'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="center muted">No cash or bank account is on the chart.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="k">TOTAL CASH (ending balance)</td>
            <td class="v">{{ $cash['total'] }}</td>
        </tr>
        <tr>
            <td class="k">Less: Cash In Bank</td>
            <td class="v">{{ $cash['in_bank'] }}</td>
        </tr>
        <tr class="grand">
            <td class="k">TOTAL CASH ON HAND</td>
            <td class="v">{{ $cash['on_hand'] }}</td>
        </tr>
    </table>

    <table class="sigs avoid-break">
        <tr>
            <td>
                <div class="sig-name" style="text-decoration:none;">{{ $letterhead['signatories']['treasurer'] ?? '' }}&nbsp;</div>
                <div class="sig-line">Prepared by · HOA Treasurer</div>
            </td>
            <td>
                <div class="sig-name" style="text-decoration:none;">{{ $letterhead['signatories']['president'] ?? '' }}&nbsp;</div>
                <div class="sig-line">Noted by · HOA President</div>
            </td>
        </tr>
    </table>

    @include('pdf.partials.printed-note', ['note' => 'read from the posted journal by ' . $generatedBy . '.'])
@endsection
