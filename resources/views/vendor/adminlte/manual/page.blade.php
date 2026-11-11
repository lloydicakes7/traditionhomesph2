@extends('adminlte::master')

@section('title', $title)

@section('classes_body', 'hold-transition layout-fixed layout-navbar-fixed sidebar-mini')

@section('adminlte_css')
    <style>
        .manual-section { scroll-margin-top: 5rem; }
        .manual-label { font-size: .75rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #6c757d; }
    </style>
@stop

@section('body')
    <div class="wrapper">

        @include('adminlte::manual.partials.navbar')

        @include('adminlte::manual.partials.sidebar')

        <div class="content-wrapper">
            <div class="content">
                <div class="container-fluid">

                    <div class="d-flex justify-content-between align-items-start pt-3 mb-3">
                        <div>
                            <h1 class="h4 mb-0 font-weight-bold">{{ $title }}</h1>
                            @if (! empty($intro))
                                <small class="text-muted">{{ $intro }}</small>
                            @endif
                        </div>
                        <a href="{{ route($homeRoute) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-fw fa-arrow-left mr-md-1"></i>
                            <span class="d-none d-md-inline">{{ __('manual::common.dashboard') }}</span>
                        </a>
                    </div>

                    @include('adminlte::manual.partials.topic')

                </div>
            </div>
        </div>

        <footer class="main-footer">
            @include('layouts.partials.footer', ['home' => route($homeRoute)])
        </footer>

    </div>
@stop

@section('adminlte_js')
    @include('layouts.partials.sidebar-scroll')
@stop
