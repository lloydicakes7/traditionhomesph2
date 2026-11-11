@extends('adminlte::page')

@section('title', $hoa_name ?? config('thha.hoa_name', 'THHA'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
    <link rel="stylesheet" href="{{ asset('css/progress.css') }}">
@endsection

@section('content_header')
    @hasSection('content_header_custom')
        @yield('content_header_custom')
    @else
        @include('layouts.partials.content-header', ['home' => route('admin.home')])
    @endif
@stop

@section('content')
    @livewire('notifications.notification-handler')
    <x-ui.progress-stack />
    @yield('page-content')
@stop

@section('footer')
    @include('layouts.partials.footer', ['home' => route('admin.home')])
@stop

@section('js')
    @include('layouts.partials.sidebar-scroll')
    <script src="{{ asset('js/modal-loader.js') }}"></script>
    <script src="{{ asset('js/print-preview.js') }}"></script>
    <script src="{{ asset('js/contact-input.js') }}"></script>
    <script src="{{ asset('js/confirm.js') }}"></script>
    <script src="{{ asset('js/progress.js') }}"></script>
@stop
