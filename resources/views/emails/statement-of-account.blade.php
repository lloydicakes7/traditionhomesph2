@component('mail::message')
# Statement of Account

Hello, {{ $summary['owner'] }}!

Here is your account with the association for **{{ $summary['period'] }}**.

{{ $summary['property'] . ' — ' . $summary['address'] }}

@component('mail::table')
| Reference | Type | Period / Date | Amount | Balance |
| :-------- | :--- | :------------ | -----: | ------: |
@foreach ($summary['bills'] as $bill)
| {{ $bill->reference }} | {{ $bill->type_label }} | {{ $bill->billing_period_display !== '—' ? $bill->billing_period_display : $bill->bill_date_display }} | {{ $bill->total_display }} | {{ $bill->balance_display }} |
@endforeach
| | | **Total outstanding** | | **{{ $summary['balance_display'] }}** |
@endcomponent

@if ($summary['instructions'])
{!! $summary['instructions'] !!}
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
