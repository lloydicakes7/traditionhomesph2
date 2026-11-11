@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $verification_resend_url = View::getSection('verification_resend_url') ?? config('adminlte.verification_resend_url', '') )

@if (config('adminlte.use_route_url', false))
    @php( $verification_resend_url = $verification_resend_url ? route($verification_resend_url) : '' )
@else
    @php( $verification_resend_url = $verification_resend_url ? url($verification_resend_url) : '' )
@endif

@section('auth_body')

    @if(session('resent'))
        <x-adminlte-alert theme="success" icon="fas fa-fw fa-check">
            {{ __('adminlte::adminlte.verify_email_sent') }}
        </x-adminlte-alert>
    @endif

    <p class="text-muted mb-3">
        {{ __('adminlte::adminlte.verify_check_your_email') }}
        {{ __('adminlte::adminlte.verify_if_not_recieved') }}
    </p>

    @if($verification_resend_url)
        <form method="POST" action="{{ $verification_resend_url }}">
            @csrf

            <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-paper-plane"
                label="{{ __('adminlte::adminlte.send_verification_link') }}" class="btn-block" />
        </form>
    @endif

@stop
