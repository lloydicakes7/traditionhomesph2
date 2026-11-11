@extends('layouts.admin')

@section('title', 'Bookings')

@section('breadcrumb')
    <li class="breadcrumb-item">Amenities Booking</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.amenities-booking.bookings.booking-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.amenities-booking.bookings.modals.booking-view-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-form-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-approve-modal')
        @livewire('admin.collections.invoices.modals.invoice-form-modal')
        @livewire('admin.collections.modals.record-payment-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-complete-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-refund-deposit-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-forfeit-deposit-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-cancel-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-delete-modal')
        @livewire('admin.amenities-booking.bookings.modals.booking-mass-delete-modal')
    </div>
@endsection
