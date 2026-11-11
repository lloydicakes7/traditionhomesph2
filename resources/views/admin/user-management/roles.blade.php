@extends('layouts.admin')

@section('title', 'User Roles')

@section('breadcrumb')
    <li class="breadcrumb-item">User Management</li>
@endsection

@section('page-content')
    @livewire('admin.user-management.role.role-list')

    {{-- Per-page modals (event-driven siblings) --}}
    @livewire('admin.user-management.role.modals.role-form-modal')
    @livewire('admin.user-management.role.modals.role-delete-modal')
@endsection
