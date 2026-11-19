@extends('layouts.portal')

@section('title', 'Complaints')

@section('page-content')
    <div>
        @livewire('portal.complaints.complaint-list')

        {{-- The complaint form and its details, edit and deletion dialogs
             (event-driven siblings) --}}
        @livewire('portal.complaints.modals.complaint-request-modal')
        @livewire('portal.complaints.modals.complaint-view-modal')
        @livewire('portal.complaints.modals.complaint-edit-modal')
        @livewire('portal.complaints.modals.complaint-delete-modal')
    </div>
@endsection
