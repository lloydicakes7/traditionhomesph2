@extends('layouts.admin')

@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item">User Management</li>
@endsection

@section('page-content')
    @livewire('admin.user-management.user.user-list')

    {{-- Per-page modals (event-driven siblings) --}}
    @livewire('admin.user-management.user.modals.user-form-modal')
    @livewire('admin.user-management.user.modals.admin-reset-password-modal')
    @livewire('admin.user-management.user.modals.user-view-modal')
    @livewire('admin.user-management.user.modals.user-delete-modal')
    @livewire('admin.user-management.user.modals.user-activity-delete-modal')
@endsection
