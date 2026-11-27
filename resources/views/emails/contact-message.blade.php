@component('mail::message')
<h1>{{ __('public.contact.mail_heading') }}</h1>

<p>The following inquiry was submitted through the association website.</p>

<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
{{ $inquiry }}
</td>
</tr>
</table>

<div class="table">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<thead>
<tr>
<th align="left">Detail</th>
<th align="left">Information</th>
</tr>
</thead>
<tbody>
<tr>
<td>{{ __('public.contact.mail_from') }}</td>
<td>{{ $senderName }}</td>
</tr>
<tr>
<td>{{ __('public.contact.sender_email') }}</td>
<td>{{ $senderEmail }}</td>
</tr>
<tr>
<td>{{ __('public.contact.contact_number') }}</td>
<td>{{ $senderContact ?: 'Not provided' }}</td>
</tr>
<tr>
<td>{{ __('public.contact.subject') }}</td>
<td>{{ $subjectLine }}</td>
</tr>
</tbody>
</table>
</div>

<p>{{ $hoa_name }}</p>
@endcomponent
