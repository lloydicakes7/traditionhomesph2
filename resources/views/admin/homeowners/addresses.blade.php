@extends('layouts.admin')

@section('title', 'Addresses')

@section('breadcrumb')
    <li class="breadcrumb-item">Homeowners</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.homeowners.address.address-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.homeowners.address.modals.address-form-modal')
        @livewire('admin.homeowners.address.modals.address-delete-modal')
    </div>
@endsection
