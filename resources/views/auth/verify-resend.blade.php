@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', __('auth.resend_verification'))
@section('dashboard_url', route('home'))

@section('auth_header', __('auth.resend_verification'))

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            <i class="fas fa-fw fa-check-circle mr-1"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-fw fa-exclamation-circle mr-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <p class="mb-3">{{ __('auth.unverified') }} {{ __('auth.verification_resend_intro') }}</p>

    <form action="{{ route('verification.resend.store') }}" method="post">
        @csrf

        <x-adminlte-input name="email" type="email" label="{{ __('adminlte::adminlte.email') }}"
            placeholder="{{ __('adminlte::adminlte.email') }}" value="{{ $verificationEmail }}"
            autocomplete="email" autofocus required enable-old-support disable-feedback>
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-paper-plane"
            label="{{ __('auth.resend_verification') }}" class="btn-block" />
    </form>
@stop

@section('js')
    <script src="{{ asset('js/required-field.js') }}"></script>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ route('login') }}">{{ __('auth.return_to_login') }}</a>
    </p>
@stop
