@component('mail::message')
# {{ __('public.contact.mail_heading') }}

{{ $inquiry }}

**{{ __('public.contact.mail_from') }}** {{ $senderName }}<br>
**{{ __('public.contact.sender_email') }}** {{ $senderEmail }}<br>
**{{ __('public.contact.contact_number') }}** {{ $senderContact ?: '—' }}<br>
**{{ __('public.contact.subject') }}** {{ $subjectLine }}
@endcomponent
