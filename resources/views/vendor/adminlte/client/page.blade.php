@extends('adminlte::master')

@section('title_prefix', config('portal.title_prefix', ''))
@section('title_postfix', config('portal.title_postfix', ''))

@section('classes_body', config('portal.classes_body', 'hold-transition layout-fixed layout-navbar-fixed sidebar-mini'))

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/client.css') }}">

    @stack('css')
    @yield('css')
@stop

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @include('adminlte::client.partials.common.preloader')

        {{-- Top Navbar --}}
        @include('adminlte::client.partials.navbar.navbar')

        {{-- Left Main Sidebar --}}
        @include('adminlte::client.partials.sidebar.left-sidebar')

        {{-- Content Wrapper --}}
        @include('adminlte::client.partials.cwrapper.cwrapper-default')

        {{-- Footer --}}
        @include('adminlte::client.partials.footer.footer')

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
