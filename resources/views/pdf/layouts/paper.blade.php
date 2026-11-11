@extends('pdf.layouts.base')
{{--
    Framed paper form (permits, certification, endorsement): letterhead,
    officers down the left, content on the right, brand footer.
    A child fills `title` and `content`.
--}}
@section('page')
    @include('pdf.partials.paper-open')
    @yield('content')
    @include('pdf.partials.paper-close')
@endsection
