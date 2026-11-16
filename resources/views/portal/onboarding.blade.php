@extends('layouts.portal')

@section('title', 'Complete Your Profile')

@section('page-content')
    @livewire('portal.onboarding.profile-wizard')
@endsection

@push('js')
    <script src="{{ asset('js/camera.js') }}"></script>
@endpush
