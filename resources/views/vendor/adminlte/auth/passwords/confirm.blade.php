@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_confirm_url = View::getSection('password_confirm_url') ?? config('adminlte.password_confirm_url', 'password/confirm') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $password_confirm_url = $password_confirm_url ? route($password_confirm_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $password_confirm_url = $password_confirm_url ? url($password_confirm_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_body')
    <p class="text-muted small mb-3">
        {{ __('adminlte::adminlte.confirm_password_message') }}
    </p>

    <form action="{{ $password_confirm_url }}" method="post">
        @csrf

        {{-- Password field --}}
        <x-adminlte-input name="password" type="password" label="{{ __('adminlte::adminlte.password') }}"
            placeholder="{{ __('adminlte::adminlte.password') }}"
            autocomplete="current-password" autofocus required>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Confirm password button --}}
        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-check"
            label="{{ __('adminlte::adminlte.confirm_password') }}" class="btn-block" />
    </form>
@stop

@section('auth_footer')
    @if($password_reset_url)
        <p class="my-0 text-center">
            <a href="{{ $password_reset_url }}" class="small font-weight-bold">
                {{ __('adminlte::adminlte.forgot_password') }}
            </a>
        </p>
    @endif
@stop
