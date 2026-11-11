@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', __('auth.pending_title'))
@section('dashboard_url', route('home'))

@section('auth_body')
    <p class="mb-3">{{ __('auth.pending_body') }}</p>

    <a href="{{ route('login') }}" class="btn btn-primary btn-block">
        <i class="fas fa-fw fa-sign-in-alt mr-1"></i>{{ __('auth.pending_sign_in') }}
    </a>
@stop
