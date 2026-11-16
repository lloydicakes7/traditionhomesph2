@extends('layouts.admin')

@section('title', __('users.verification.title'))

@section('page-content')
    <div>
        @can('viewProfileVerifications', \App\Models\User::class)
            @livewire('admin.homeowners.verification.profile-verification-table-list')

            @livewire('admin.homeowners.verification.modals.profile-verification-view-modal')
        @endcan
    </div>
@endsection
