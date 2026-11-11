@extends('layouts.admin')

@section('title', 'Amenities')

@section('breadcrumb')
    <li class="breadcrumb-item">Amenities Booking</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.amenities-booking.amenities.amenity-card-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.amenities-booking.amenities.modals.amenity-view-modal')
        @livewire('admin.amenities-booking.amenities.modals.amenity-form-modal')
        @livewire('admin.amenities-booking.amenities.modals.amenity-delete-modal')
    </div>
@endsection
