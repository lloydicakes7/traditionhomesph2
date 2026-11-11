@extends('layouts.admin')

@section('title', 'Households')

@section('breadcrumb')
    <li class="breadcrumb-item">Homeowners</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.homeowners.household.household-stats')
        @livewire('admin.homeowners.household.household-csv')
        @livewire('admin.homeowners.household.household-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.homeowners.household.modals.household-form-modal')
        @livewire('admin.homeowners.household.modals.household-view-modal')
        @livewire('admin.homeowners.household.modals.household-delete-modal')

        {{-- Shared, multi-page modals --}}
        @livewire('admin.modals.edit-members-modal')
    </div>
@endsection
