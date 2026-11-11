@extends('layouts.admin')

@section('title', 'Homeowners')

@section('page-content')
    <div>
        @can('viewAny', \App\Models\User::class)
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('homeowners.pending-registrations') }}"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-fw fa-user-clock mr-1"></i>Pending Registrations
                </a>
            </div>
        @endcan

        @livewire('admin.homeowners.homeowner.homeowner-stats')
        @livewire('admin.homeowners.homeowner.homeowner-table-list')

        @livewire('admin.homeowners.homeowner.modals.homeowner-form-modal')
        @livewire('admin.homeowners.homeowner.modals.homeowner-view-modal')
        @livewire('admin.homeowners.homeowner.modals.homeowner-reset-password-modal')
        @livewire('admin.homeowners.homeowner.modals.homeowner-delete-modal')
        @livewire('admin.homeowners.homeowner.modals.homeowner-mass-delete-modal')

        @livewire('admin.modals.link-resident-user-modal', ['mode' => 'to-user'])
    </div>
@endsection
