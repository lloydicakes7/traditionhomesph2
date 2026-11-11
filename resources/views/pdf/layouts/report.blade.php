@extends('pdf.layouts.base')
{{--
    Records and reports (RBI, residents list, voucher, notice): letterhead,
    content, the settings footer, running title + page number. A child fills
    `title`, `running_header` and `content`; it closes itself (printed-note) if
    it wants to.
--}}
@section('page')
    <div class="page">
        @include('pdf.partials.letterhead')
        @yield('content')
        @include('pdf.partials.footer-paper')
    </div>
@endsection
