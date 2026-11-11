@extends('layouts.portal')

@section('title', 'My Bookings')

@section('page-content')
    <div>
        @livewire('portal.bookings.booking-list')

        {{-- The request form and the delete confirmation (event-driven siblings) --}}
        @livewire('portal.bookings.modals.booking-request-modal')
        @livewire('portal.bookings.modals.booking-delete-modal')
    </div>
@endsection
