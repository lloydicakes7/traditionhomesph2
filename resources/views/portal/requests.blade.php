@extends('layouts.portal')

@section('title', 'Requests')

@section('page-content')
    <div>
        @livewire('portal.requests.request-list')

        {{-- The request form and the deletion confirmation (event-driven siblings) --}}
        @livewire('portal.requests.modals.request-file-modal')
        @livewire('portal.requests.modals.request-withdraw-modal')
    </div>
@endsection
