@extends('layouts.portal')

@section('title', 'Complaints')

@section('page-content')
    <div>
        @livewire('portal.complaints.complaint-list')

        {{-- The complaint form (event-driven sibling) --}}
        @livewire('portal.complaints.modals.complaint-request-modal')
    </div>
@endsection
