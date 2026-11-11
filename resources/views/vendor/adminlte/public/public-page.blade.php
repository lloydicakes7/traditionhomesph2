@extends('adminlte::master')

@section('title_prefix', '')
@section('title_postfix', '')

@section('classes_body', 'hold-transition layout-top-nav')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('body')
    <div class="wrapper">

        {{-- Public top bar --}}
        @include('public.partials.top-bar', ['contact' => $contact])

        {{-- Public top navigation --}}
        @include('adminlte::public.partials.navbar.navbar', [
            'name'        => $name,
            'short_name'  => $short_name,
            'logo_path'   => $logo_path,
            'publicLinks' => $publicLinks,
            'contentMenu' => $contentMenu ?? [],
        ])

        {{-- Page content --}}
        <div class="content-wrapper">
            @yield('content')
        </div>

        {{-- Public footer --}}
        @include('adminlte::public.partials.footer.footer', [
            'name'        => $name,
            'short_name'  => $short_name,
            'description' => $description,
            'contact'     => $contact,
            'logo_path'   => $logo_path,
            'footerLinks' => $footerLinks,
        ])

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
