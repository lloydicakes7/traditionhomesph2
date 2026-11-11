@extends('layouts.admin')

@section('title', 'Properties')

@section('breadcrumb')
    <li class="breadcrumb-item">Properties</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.homeowners.property.property-stats')
        @livewire('admin.homeowners.property.property-csv')
        @livewire('admin.homeowners.property.property-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.homeowners.property.modals.property-form-modal')
        @livewire('admin.homeowners.property.modals.property-view-modal')
        @livewire('admin.homeowners.property.modals.property-delete-modal')
        @livewire('admin.homeowners.property.modals.property-mass-delete-modal')
        @livewire('admin.homeowners.property.modals.property-assign-homeowner-modal')
    </div>
@endsection
