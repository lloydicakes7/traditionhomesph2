@extends('layouts.portal')

@section('title', 'Requests')

@section('page-content')
    <div>
        @livewire('portal.requests.request-list')

        {{-- The request form, its details and edit dialogs, and the deletion
             confirmation (event-driven siblings) --}}
        @livewire('portal.requests.modals.request-file-modal', ['document' => request()->query('document')])
        @livewire('portal.requests.modals.request-view-modal')
        @livewire('portal.requests.modals.request-edit-modal')
        @livewire('portal.requests.modals.request-withdraw-modal')
    </div>
@endsection
