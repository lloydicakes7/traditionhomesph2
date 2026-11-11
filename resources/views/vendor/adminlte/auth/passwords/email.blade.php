@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_email_url = View::getSection('password_email_url') ?? config('adminlte.password_email_url', 'password/email') )
@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@if (config('adminlte.use_route_url', false))
    @php( $password_email_url = $password_email_url ? route($password_email_url) : '' )
    @php( $login_url = $login_url ? route($login_url) : '' )
@else
    @php( $password_email_url = $password_email_url ? url($password_email_url) : '' )
    @php( $login_url = $login_url ? url($login_url) : '' )
@endif

@section('auth_body')

    @if(session('status'))
        <x-adminlte-alert theme="success" icon="fas fa-fw fa-check">
            {{ session('status') }}
        </x-adminlte-alert>
    @endif

    <p class="text-muted small mb-3">
        {{ __('adminlte::adminlte.password_reset_hint') }}
    </p>

    <form action="{{ $password_email_url }}" method="post">
        @csrf

        {{-- Email field --}}
        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}"
            autocomplete="email" autofocus enable-old-support>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Send reset link button --}}
        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-share-square"
            label="{{ __('adminlte::adminlte.send_password_reset_link') }}" class="btn-block" />
    </form>
@stop

@section('auth_footer')
    <p class="my-0 text-center">
        <a href="{{ $login_url }}" class="small font-weight-bold">
            {{ __('adminlte::adminlte.back_to_sign_in') }}
        </a>
    </p>
@stop
