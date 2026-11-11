@extends('pdf.layouts.report')
{{--
    SUMMARY REPORT — one side of the year's books, account by account and month
    by month, on a single sheet held the wide way.
    Builder: SummaryReportBuilder.

    The matrix reads in both directions: every account's row carries its twelve
    months and its total, and the closing row carries each month's total and the
    year's. An account with nothing posted all year is not printed.

    The amounts are written bare — the subtitle names PHP once rather than
    thirteen times, which is what lets thirteen money columns fit the sheet.

    @var string          $type          "Income" or "Expense"
    @var string          $heading       "INCOME BY MONTH"
    @var string          $year
    @var string          $span          "For the year 2026"
    @var string          $currency      "PHP"
    @var array<string, string> $month_labels  "01" => "Jan", ...
    @var array           $rows          [['code', 'account', 'months' => [amount, ...], 'total'], ...]
    @var array<int, string>    $months       the twelve month totals
    @var string          $year_total
    @var array           $letterhead    (signatories.treasurer, signatories.president)
    @var \Carbon\Carbon  $printedAt @var string $generatedBy
--}}
@section('title', 'Summary Report — ' . $heading . ' ' . $year)
@section('running_header', 'Summary Report · ' . $heading . ' · ' . $year)

@section('content')
    <h2 class="report-title">SUMMARY {{ $heading }} YEAR {{ $year }}</h2>

    <table class="tbl matrix">
        <thead>
            <tr>
                <th style="width:20%;">Particulars</th>
                @foreach ($month_labels as $label)
                    <th class="amount" style="width:6%;">{{ $label }}</th>
                @endforeach
                <th class="amount" style="width:8%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['account'] }}</td>
                    @foreach ($row['months'] as $amount)
                        <td class="amount">{{ $amount }}</td>
                    @endforeach
                    <td class="amount">{{ $row['total'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="center muted">
                        Nothing was posted to the {{ strtolower($type) }} accounts in {{ $year }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="right">TOTAL</td>
                @foreach ($months as $amount)
                    <td class="amount">{{ $amount }}</td>
                @endforeach
                <td class="amount">{{ $year_total }}</td>
            </tr>
        </tfoot>
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
