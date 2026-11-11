@extends('layouts.admin')

@section('title', 'Collections')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.settings.collections-settings')

        @livewire('admin.settings.modals.payment-method-form-modal')
        @livewire('admin.settings.modals.payment-method-delete-modal')
    </div>
@endsection
