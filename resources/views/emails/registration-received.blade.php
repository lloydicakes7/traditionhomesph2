@component('mail::message')
# {{ __('auth.registration_received_heading') }}

{{ __('auth.registration_received_greeting', ['name' => $user->name]) }}

{{ __('auth.registration_received_body') }}

**{{ __('auth.registration_received_email') }}** {{ $user->email }}

@if ($user->is_active)
{{ __('auth.registration_received_active') }}
@else
{{ __('auth.registration_received_pending') }}
@endif

{{ __('auth.registration_received_ignore') }}

{{ __('auth.registration_received_regards') }}<br>
{{ config('app.name') }}
@endcomponent
