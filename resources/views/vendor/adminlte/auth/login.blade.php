@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_body')
    <form action="{{ $login_url }}" method="post">
        @csrf

        {{-- Email field --}}
        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}"
            autocomplete="email" autofocus enable-old-support>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Password field --}}
        <x-adminlte-input name="password" type="password" label="{{ __('adminlte::adminlte.password') }}"
            placeholder="{{ __('adminlte::adminlte.password') }}"
            autocomplete="current-password">
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        {{-- Remember me + Forgot password, same row --}}
        <div class="form-group d-flex justify-content-between align-items-center mb-3">
            <div class="form-check" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                <input type="checkbox" name="remember" id="remember" class="form-check-input text-primary"
                    {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="form-check-label">
                    {{ __('adminlte::adminlte.remember_me') }}
                </label>
            </div>

            @if($password_reset_url)
                <a href="{{ $password_reset_url }}" class="small font-weight-bold">
                    {{ __('adminlte::adminlte.forgot_password') }}
                </a>
            @endif
        </div>

        {{-- Sign in --}}
        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-sign-in-alt"
            label="{{ __('adminlte::adminlte.sign_in') }}" class="btn-block" />
    </form>
@stop

@section('auth_footer')
    @if($register_url)
        <p class="my-0 mt-2 text-center small text-muted">
            {{ __('adminlte::adminlte.no_account') }}
            <a href="{{ $register_url }}" class="font-weight-bold">
                {{ __('adminlte::adminlte.create_account') }}
            </a>
        </p>
    @endif
@stop
