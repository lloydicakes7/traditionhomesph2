@component('mail::message')
<h1>Greetings, {{ $summary['owner'] }},</h1>

<p>Below is your account with the association for <strong>{{ $summary['period'] }}</strong>.</p>

<p>{{ $summary['property'] }}<br>
{{ $summary['address'] }}</p>

@foreach ($summary['groups'] as $group)
@if ($group['bills']->isNotEmpty())
<h2>{{ $group['label'] }}</h2>

<div class="table">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<thead>
<tr>
<th align="left">Reference</th>
<th align="left">Type</th>
<th align="left">Period or date</th>
<th align="right">Amount</th>
<th align="right">Balance</th>
</tr>
</thead>
<tbody>
@foreach ($group['bills'] as $bill)
<tr>
<td>{{ $bill->reference }}</td>
<td>{{ $bill->type_label }}</td>
<td>{{ $bill->billing_period ? $bill->billing_period_display : $bill->bill_date_display }}</td>
<td align="right">{{ $bill->total_display }}</td>
<td align="right">{{ $bill->balance_display }}</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr>
<td colspan="4" align="right"><strong>{{ $group['label'] }} total</strong></td>
<td align="right"><strong>{{ $group['total_display'] }}</strong></td>
</tr>
</tfoot>
</table>
</div>
@endif
@endforeach

@php
    $hasBills = collect($summary['groups'])->sum(function ($group) {
        return $group['bills']->count();
    }) > 0;
@endphp

@if (! $hasBills)
<p>No charges are outstanding for this period.</p>
@endif

<p><strong>Total outstanding: {{ $summary['balance_display'] }}</strong></p>

@if ($summary['instructions'])
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
{!! $summary['instructions'] !!}
</td>
</tr>
</table>
@endif

<p>{{ $hoa_name }}</p>
@endcomponent
