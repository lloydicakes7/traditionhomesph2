@extends('layouts.admin')

@section('title', 'Complaints')

@section('breadcrumb')
    <li class="breadcrumb-item">Support</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.support.complaints.complaint-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.support.complaints.modals.complaint-form-modal')
        @livewire('admin.support.complaints.modals.complaint-view-modal')
        @livewire('admin.support.complaints.modals.complaint-respond-modal')
        @livewire('admin.support.complaints.modals.complaint-delete-modal')
        @livewire('admin.support.complaints.modals.complaint-mass-delete-modal')
    </div>
@endsection
