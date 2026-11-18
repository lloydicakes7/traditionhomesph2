@component('mail::message')
# {{ __('public.contact.mail_heading') }}

The following inquiry was submitted through the association website.

@component('mail::panel')
{{ $inquiry }}
@endcomponent

@component('mail::table')
| Detail | Information |
| :----- | :---------- |
| {{ __('public.contact.mail_from') }} | {{ $senderName }} |
| {{ __('public.contact.sender_email') }} | {{ $senderEmail }} |
| {{ __('public.contact.contact_number') }} | {{ $senderContact ?: 'Not provided' }} |
| {{ __('public.contact.subject') }} | {{ $subjectLine }} |
@endcomponent

{{ $hoa_name }}
@endcomponent
