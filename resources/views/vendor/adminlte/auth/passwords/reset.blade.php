@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_body')
    <form action="{{ $password_reset_url }}" method="post">
        @csrf

        {{-- Token field --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email field --}}
        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}"
            :value="$email ?? null" autocomplete="email" autofocus enable-old-support>
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

        {{-- Password confirmation field --}}
        <x-adminlte-input name="password_confirmation" type="password"
            label="{{ __('adminlte::adminlte.retype_password') }}"
            placeholder="{{ __('adminlte::adminlte.retype_password') }}"
            autocomplete="new-password">
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Confirm password reset button --}}
        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-sync-alt"
            label="{{ __('adminlte::adminlte.reset_password') }}" class="btn-block" />
    </form>
@stop
