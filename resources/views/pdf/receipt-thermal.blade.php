<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('payments.receipt.title') }} {{ $receipt['receipt_no'] }}</title>
    <style>
        @page { margin: 4mm 4mm 3mm 4mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8px;
            line-height: 1.35;
            color: #111;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .muted { color: #444; }
        .head .hoa { font-size: 9px; font-weight: bold; }
        .head .title { font-size: 11px; font-weight: bold; letter-spacing: 1px; margin-top: 2px; }
        .dash { border-top: 1px dashed #111; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        td.k { width: 38%; color: #333; }
        td.v { width: 62%; text-align: right; }
        .amount { font-size: 10px; font-weight: bold; }
        .words { margin-top: 2px; }
        .note { margin-top: 3px; }
        .footer { margin-top: 4px; font-size: 7px; text-align: center; }
    </style>
</head>
<body>
    <div class="head center">
        <div class="hoa">{{ $hoa_name }}</div>
        <div class="title">{{ __('payments.receipt.title') }}</div>
    </div>

    <div class="dash"></div>

    <table>
        <tr><td class="k">{{ __('payments.receipt.no') }}</td><td class="v bold">{{ $receipt['receipt_no'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.date') }}</td><td class="v">{{ $receipt['date_paid'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.received_from') }}</td><td class="v">{{ $receipt['payer'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.unit') }}</td><td class="v">{{ $receipt['unit'] }}</td></tr>
    </table>

    <div class="dash"></div>

    <table>
        <tr><td class="k">{{ __('payments.receipt.bill') }}</td><td class="v">{{ $receipt['bill_reference'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.bill_description') }}</td><td class="v">{{ $receipt['bill_type'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.period') }}</td><td class="v">{{ $receipt['bill_period'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.bill_total') }}</td><td class="v">{{ $receipt['total'] }}</td></tr>
    </table>

    <div class="dash"></div>

    <table>
        <tr><td class="k">{{ __('payments.receipt.method') }}</td><td class="v">{{ $receipt['method'] }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.reference') }}</td><td class="v">{{ $receipt['reference_no'] ?: '—' }}</td></tr>
        <tr><td class="k">{{ __('payments.receipt.balance_after') }}</td><td class="v">{{ $receipt['balance_after'] }}</td></tr>
    </table>

    <div class="dash"></div>

    <table>
        <tr>
            <td class="k">{{ __('payments.receipt.amount_paid') }}</td>
            <td class="v amount">{{ $receipt['amount_paid'] }}</td>
        </tr>
    </table>
    <div class="words">{{ $receipt['amount_in_words'] }}</div>

    @if ($receipt['notes'])
        <div class="note muted">{{ __('payments.receipt.notes') }}: {{ $receipt['notes'] }}</div>
    @endif

    <div class="dash"></div>

    <table>
        <tr>
            <td>{{ __('payments.receipt.received_by') }}</td>
            <td class="right bold">{{ $receipt['received_by'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="right muted">{{ $receipt['received_by_role'] }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ __('payments.receipt.footer') }}<br>
        {{ $printedAt->format('M j, Y g:i A') }}
    </div>
</body>
</html>
