@extends('layouts.admin')

@section('title', 'Residents')

@section('breadcrumb')
    <li class="breadcrumb-item">Homeowners</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.homeowners.resident.resident-stats')
        @livewire('admin.homeowners.resident.resident-csv')
        @livewire('admin.homeowners.resident.resident-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.homeowners.resident.modals.resident-form-modal')
        @livewire('admin.homeowners.resident.modals.resident-view-modal')
        @livewire('admin.homeowners.resident.modals.resident-delete-modal')
        @livewire('admin.homeowners.resident.modals.resident-mass-delete-modal')

        {{-- Shared, multi-page modals --}}
        @livewire('admin.modals.link-resident-user-modal', ['mode' => 'to-resident'])
    </div>
@endsection
