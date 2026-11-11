@extends('adminlte::master')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboard_url = $dashboard_url ? route($dashboard_url) : '';
    } else {
        $dashboard_url = $dashboard_url ? url($dashboard_url) : '';
    }

    $bodyClasses = ($auth_type ?? 'login') . '-page';

    $logo = config('adminlte.auth_logo.enabled', false)
        ? config('adminlte.auth_logo.img')
        : [
            'path'   => config('adminlte.logo_img'),
            'alt'    => config('adminlte.logo_img_alt'),
            'class'  => config('adminlte.auth_logo.img.class'),
            'width'  => 48,
            'height' => 48,
        ];
@endphp

@section('title_prefix', '')
@section('title_postfix', '')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ $bodyClasses }}@stop

@section('body')
    <div class="{{ $auth_type ?? 'login' }}-box">

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', '') }}">

            {{-- Card Header — logo and page title, left aligned --}}
            <div class="card-header d-flex justify-content-center align-items-center {{ config('adminlte.classes_auth_header', '') }}">
                <a href="{{ $dashboard_url }}" class="mr-2">
                    <img src="{{ asset($logo['path']) }}"
                         alt="{{ $logo['alt'] }}"
                         class="{{ $logo['class'] }}"
                         width="{{ $logo['width'] }}"
                         height="{{ $logo['height'] }}">
                </a>

                @hasSection('title')
                    <h1 class="card-title text-secondary text-uppercase">
                        @yield('title')
                    </h1>
                @endif
            </div>

            {{-- Card Body --}}
            <div class="card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
