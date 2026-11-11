@extends('adminlte::auth.login')

@section('title', config('thha.hoa_short_name'))
@section('dashboard_url', route('home'))
@section('login_url', route('login'))
@section('password_reset_url', route('password.request'))

@section('auth_body')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-fw fa-exclamation-circle mr-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="post">
        @csrf

        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}"
            autocomplete="email" autofocus enable-old-support disable-feedback>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="password" type="password" label="{{ __('adminlte::adminlte.password') }}"
            placeholder="{{ __('adminlte::adminlte.password') }}"
            autocomplete="current-password" disable-feedback>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
            </x-slot>
        </x-adminlte-input>

        <div class="form-group d-flex justify-content-between align-items-center mb-3">
            <div class="form-check" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                <input type="checkbox" name="remember" id="remember" value="1" class="form-check-input text-primary"
                    {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="form-check-label">
                    {{ __('adminlte::adminlte.remember_me') }}
                </label>
            </div>

            <a href="{{ route('password.request') }}" class="small font-weight-bold">
                {{ __('adminlte::adminlte.forgot_password') }}
            </a>
        </div>

        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-sign-in-alt"
            label="{{ __('adminlte::adminlte.sign_in') }}" class="btn-block" />
    </form>
@stop
