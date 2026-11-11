@extends('layouts.admin')

@section('title', 'Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.invoices.invoice-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.invoices.modals.invoice-view-modal')
        @livewire('admin.collections.invoices.modals.invoice-form-modal')
        @livewire('admin.collections.invoices.modals.invoice-delete-modal')
        @livewire('admin.collections.invoices.modals.invoice-cancel-modal')
        @livewire('admin.collections.invoices.modals.invoice-mass-delete-modal')
        @livewire('admin.collections.products.modals.product-form-modal')
        @livewire('admin.collections.modals.record-payment-modal')
    </div>
@endsection
