@extends('layouts.admin')

@section('title', 'Monthly Dues Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.monthly-dues.monthly-due-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.monthly-dues.modals.past-monthly-due-modal')
        @livewire('admin.collections.monthly-dues.modals.monthly-due-view-modal')
        @livewire('admin.collections.monthly-dues.modals.monthly-due-delete-modal')
        @livewire('admin.collections.monthly-dues.modals.monthly-due-cancel-modal')
        @livewire('admin.collections.monthly-dues.modals.monthly-due-mass-delete-modal')
        @livewire('admin.collections.modals.record-payment-modal')
    </div>
@endsection
