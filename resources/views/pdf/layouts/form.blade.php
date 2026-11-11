@extends('pdf.layouts.base')
{{--
    Full-width form (membership form, sinumpaang salaysay): letterhead, content
    across the page, brand footer. A child fills `title` and `content`.
--}}
@section('page')
    <div class="page">
        @include('pdf.partials.letterhead', ['withRule' => $withRule ?? true])
        @yield('content')
        @include('pdf.partials.footer-paper')
    </div>
@endsection
