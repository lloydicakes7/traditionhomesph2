@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
@endif

@section('auth_body')
    <form action="{{ $register_url }}" method="post">
        @csrf

        {{-- Name field --}}
        <x-adminlte-input name="name" label="{{ __('adminlte::adminlte.full_name') }}"
            placeholder="{{ __('adminlte::adminlte.full_name') }}"
            autocomplete="name" autofocus enable-old-support>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Email field --}}
        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}"
            autocomplete="email" enable-old-support>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Password field --}}
        <x-adminlte-input name="password" type="password" label="{{ __('adminlte::adminlte.password') }}"
            placeholder="{{ __('adminlte::adminlte.password') }}"
            autocomplete="new-password">
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Confirm password field --}}
        <x-adminlte-input name="password_confirmation" type="password"
            label="{{ __('adminlte::adminlte.retype_password') }}"
            placeholder="{{ __('adminlte::adminlte.retype_password') }}"
            autocomplete="new-password">
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Register button --}}
        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-user-plus"
            label="{{ __('adminlte::adminlte.register') }}" class="btn-block" />
    </form>
@stop

@section('auth_footer')
    <p class="my-0 text-center small text-muted">
        {{ __('adminlte::adminlte.have_account') }}
        <a href="{{ $login_url }}" class="font-weight-bold">
            {{ __('adminlte::adminlte.sign_in_here') }}
        </a>
    </p>
@stop
