@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', __('auth.verify_expired_title'))
@section('dashboard_url', route('home'))

@section('auth_body')
    <p class="mb-3">{{ __('auth.verify_expired_body') }}</p>

    <form action="{{ route('verification.resend.store') }}" method="post">
        @csrf
        <input type="hidden" name="email" value="{{ $verificationEmail }}">

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-fw fa-paper-plane mr-1"></i>{{ __('auth.resend_verification') }}
        </button>
    </form>

    <a href="{{ route('login') }}" class="btn btn-link btn-block mt-2">
        {{ __('auth.back_to_sign_in') }}
    </a>
@stop
