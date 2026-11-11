@extends('pdf.layouts.report')

@section('title', __('payments.receipt.title') . ' ' . $receipt['receipt_no'])
@section('running_header', __('payments.receipt.title') . ' · ' . $receipt['receipt_no'])

@section('styles')
    <style>{!! $styles !!}</style>
@endsection

@section('content')
    {{-- ===== Header ===== --}}
    <table class="receipt-head">
        <tr>
            <td>
                <div class="receipt-title">{{ __('payments.receipt.title') }}</div>
                <div class="receipt-subtitle">{{ $receipt['bill_type'] }}</div>
            </td>
            <td>
                <table class="kv meta receipt-meta">
                    <tr>
                        <td class="k">{{ __('payments.receipt.no') }}</td>
                        <td class="v">{{ $receipt['receipt_no'] }}</td>
                    </tr>
                    <tr>
                        <td class="k">{{ __('payments.receipt.date') }}</td>
                        <td class="v">{{ $receipt['date_paid'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== Parties ===== --}}
    <table class="kv receipt-parties">
        <tr>
            <td class="k">{{ __('payments.receipt.received_from') }}</td>
            <td class="v">{{ $receipt['payer'] }}</td>
            <td class="k">{{ __('payments.receipt.unit') }}</td>
            <td class="v">{{ $receipt['unit'] }}</td>
        </tr>
    </table>

    {{-- ===== Payment for ===== --}}
    <div class="sect">{{ __('payments.receipt.payment_for') }}</div>

    <table class="kv meta receipt-bill">
        <tr>
            <td class="k">{{ __('payments.receipt.bill') }}</td>
            <td class="v">{{ $receipt['bill_reference'] }}</td>
            <td class="k">{{ __('payments.receipt.period') }}</td>
            <td class="v">{{ $receipt['bill_period'] }}</td>
        </tr>
        <tr>
            <td class="k">{{ __('payments.receipt.bill_description') }}</td>
            <td class="v" colspan="3">{{ $receipt['bill_type'] }}</td>
        </tr>
    </table>

    <table class="tbl plain receipt-items">
        <thead>
            <tr>
                <th>{{ __('payments.receipt.item') }}</th>
                <th class="amount receipt-qty">{{ __('payments.receipt.qty') }}</th>
                <th class="amount receipt-price">{{ __('payments.receipt.unit_price') }}</th>
                <th class="amount receipt-amount">{{ __('payments.receipt.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt['items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ $item['quantity'] }}</td>
                    <td class="amount">{{ $item['unit_price'] }}</td>
                    <td class="amount">{{ $item['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right">{{ __('payments.receipt.bill_total') }}</td>
                <td class="amount">{{ $receipt['total'] }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ===== Details ===== --}}
    <div class="sect">{{ __('payments.receipt.details') }}</div>

    <table class="kv receipt-details">
        <tr>
            <td class="k">{{ __('payments.receipt.method') }}</td>
            <td class="v">{{ $receipt['method'] }}</td>
            <td class="k">{{ __('payments.receipt.reference') }}</td>
            <td class="v">{{ $receipt['reference_no'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="k">{{ __('payments.receipt.amount_paid') }}</td>
            <td class="v receipt-amount-paid">{{ $receipt['amount_paid'] }}</td>
            <td class="k">{{ __('payments.receipt.balance_after') }}</td>
            <td class="v">{{ $receipt['balance_after'] }}</td>
        </tr>
        <tr>
            <td class="k">{{ __('payments.receipt.amount_in_words') }}</td>
            <td class="v plain" colspan="3">{{ $receipt['amount_in_words'] }}</td>
        </tr>
    </table>

    @if ($receipt['notes'])
        <div class="sect">{{ __('payments.receipt.notes') }}</div>
        <div class="small">{{ $receipt['notes'] }}</div>
    @endif

    {{-- ===== Signatures ===== --}}
    <table class="sigs receipt-signatures">
        <tr>
            <td>
                <div class="receipt-blank"></div>
                <div class="sig-line">{{ __('payments.receipt.payer_signature') }}</div>
            </td>
            <td>
                <div class="sig-name">{{ $receipt['received_by'] }}</div>
                <div class="sig-title">{{ $receipt['received_by_role'] }}</div>
                <div class="sig-line">{{ __('payments.receipt.received_by') }}</div>
            </td>
        </tr>
    </table>

    {{-- ===== Footer ===== --}}
    @include('pdf.partials.printed-note', ['note' => __('payments.receipt.footer')])
@endsection
