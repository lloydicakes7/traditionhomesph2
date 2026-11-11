@extends('pdf.layouts.report')
{{--
    JOURNAL VOUCHER — one entry's debit and credit lines.
    Builder: JournalVoucherBuilder.

    @var array|null     $voucher   null when no entry was chosen, else:
                                   [entryNo, date, classification, source, reference, description,
                                    postedBy, lines => [[code, account, memo, debit, credit], ...]  (amounts pre-formatted, "—" when zero),
                                    totalDebit, totalCredit, balanced (bool)]
    @var \Carbon\Carbon $printedAt
--}}
@section('title', 'Journal Voucher' . ($voucher ? ' ' . $voucher['entryNo'] : ''))
@section('running_header', 'Journal Voucher' . ($voucher ? ' · ' . $voucher['entryNo'] : ''))

@section('content')
    <h2 class="report-title">Journal Voucher</h2>

    @if (! $voucher)
        <p class="report-subtitle">No journal entry was chosen.</p>
    @else
        <table class="kv meta">
            <tr>
                <td class="k">Entry No.</td><td class="v">{{ $voucher['entryNo'] }}</td>
                <td class="k">Date</td><td class="v">{{ $voucher['date'] }}</td>
            </tr>
            <tr>
                <td class="k">Source</td><td class="v">{{ $voucher['source'] }}</td>
                <td class="k">Classification</td><td class="v">{{ $voucher['classification'] }}</td>
            </tr>
            <tr>
                <td class="k">Reference #</td><td class="v">{{ $voucher['reference'] }}</td>
                <td class="k">Memo</td><td class="v">{{ $voucher['description'] }}</td>
            </tr>
            <tr>
                <td class="k">Recorded by</td><td class="v" colspan="3">{{ $voucher['postedBy'] }}</td>
            </tr>
        </table>

        <div class="spacer-m"></div>

        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:10%;">Code</th>
                    <th style="width:30%;">Account</th>
                    <th>Memo</th>
                    <th class="amount" style="width:16%;">Debit</th>
                    <th class="amount" style="width:16%;">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voucher['lines'] as $line)
                    <tr>
                        <td>{{ $line['code'] }}</td>
                        <td>{{ $line['account'] }}</td>
                        <td>{{ $line['memo'] }}</td>
                        <td class="amount">{{ $line['debit'] }}</td>
                        <td class="amount">{{ $line['credit'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right">Totals</td>
                    <td class="amount">{{ $voucher['totalDebit'] }}</td>
                    <td class="amount">{{ $voucher['totalCredit'] }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="sigs">
            <tr>
                <td><div class="sig-line">Prepared by</div></td>
                <td><div class="sig-line">Approved by</div></td>
                <td><div class="sig-line">Received by</div></td>
            </tr>
        </table>

        @if ($voucher['balanced'])
            @include('pdf.partials.printed-note', ['note' => 'equal debits and credits, every time.'])
        @else
            <div class="printed-note"><span class="out-of-balance">OUT OF BALANCE — debits and credits differ. Do not file.</span></div>
        @endif
    @endif
@endsection
