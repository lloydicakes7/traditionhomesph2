@extends('adminlte::master')

@section('title_prefix', '')
@section('title', config('thha.hoa_short_name'))
@section('title_postfix', '')

@section('classes_body', 'hold-transition login-page')

@section('body')
    <div class="container py-4" style="height: 100vh;">
        <div class="h-100 w-100 row justify-content-center align-items-center">
            <div class="col-12 col-xl-10">

                @livewire('auth.register-wizard')

            </div>
        </div>
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    <script src="{{ asset('js/contact-input.js') }}"></script>
    <script src="{{ asset('js/camera.js') }}"></script>
@stop
