@extends('layouts.admin')

@section('title', 'Profile')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.settings.profile-settings')

        @unless(Auth::user()->hasRole(\App\Enums\DefaultRoleSlug::ADMINISTRATOR()))
            @livewire('admin.settings.modals.delete-account-modal')
        @endunless
    </div>
@endsection
