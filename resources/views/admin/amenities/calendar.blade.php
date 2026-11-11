@extends('layouts.admin')

@section('title', 'Calendar View')

@section('breadcrumb')
    <li class="breadcrumb-item">Amenities Booking</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.amenities-booking.calendar.booking-calendar')

        {{-- The calendar opens the same reservation dialogs the bookings page
             does, so they are the same components. --}}
        @livewire('admin.amenities-booking.bookings.modals.booking-view-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-form-modal')
    </div>
@endsection
