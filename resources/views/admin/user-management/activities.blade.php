@extends('layouts.admin')

@section('title', 'User Activities')

@section('breadcrumb')
    <li class="breadcrumb-item">User Management</li>
@endsection

@section('page-content')
    @livewire('admin.user-management.activity.activity-list')

    @livewire('admin.user-management.user.modals.user-activity-delete-modal')
    @livewire('admin.user-management.activity.modals.activity-mass-delete-modal')
@endsection
