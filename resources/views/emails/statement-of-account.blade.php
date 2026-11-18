@component('mail::message')
# Greetings, {{ $summary['owner'] }},

Below is your account with the association for **{{ $summary['period'] }}**.

{{ $summary['property'] }}<br>
{{ $summary['address'] }}

@php
    $rows = collect($summary['bills'])->map(function ($bill) {
        $period = $bill->billing_period ? $bill->billing_period_display : $bill->bill_date_display;

        return '| ' . $bill->reference . ' | ' . $bill->type_label . ' | ' . $period . ' | ' . $bill->total_display . ' | ' . $bill->balance_display . ' |';
    })->implode("\n");
@endphp

@component('mail::table')
| Reference | Type | Period or date | Amount | Balance |
| :-------- | :--- | :------------- | -----: | ------: |
{!! $rows !!}
| **Total outstanding** | | | | **{{ $summary['balance_display'] }}** |
@endcomponent

@if ($summary['instructions'])
{!! $summary['instructions'] !!}
@endif

{{ $hoa_name }}
@endcomponent
