@extends('layouts.public')

@section('title', ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.sections.home.hero')

    @include('public.sections.home.stats')

    @include('public.sections.home.features')

    @include('public.sections.home.announcements')

    @include('public.sections.home.portal-features')

    @include('public.sections.home.portal-cta')

    @include('public.sections.home.members')

    @include('public.sections.home.location')

    @include('public.sections.home.faq')
@endsection
