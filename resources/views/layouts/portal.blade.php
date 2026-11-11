@extends('adminlte::client.page')

@section('title', $hoa_name ?? config('thha.hoa_name', 'THHA'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/progress.css') }}">
@endsection

@section('content_header')
    @include('layouts.partials.content-header', ['home' => route('portal.dashboard')])
@stop

@section('content')
    @livewire('notifications.notification-handler')
    <x-ui.progress-stack />
    @yield('page-content')
@stop

@section('js')
    @include('layouts.partials.sidebar-scroll')
    <script src="{{ asset('js/modal-loader.js') }}"></script>
    <script src="{{ asset('js/contact-input.js') }}"></script>
    <script src="{{ asset('js/progress.js') }}"></script>
@stop
