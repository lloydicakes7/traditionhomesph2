@extends('layouts.admin')

@section('title', 'Registrations')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <a href="{{ route('homeowners.homeowners') }}">Homeowners</a>
    </li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.homeowners.homeowner.pending-registration-table-list')

        @livewire('admin.homeowners.homeowner.modals.pending-registration-view-modal')
        @livewire('admin.homeowners.homeowner.modals.homeowner-delete-modal')
    </div>
@endsection
