@extends('layouts.admin')

@section('title', 'Requests')

@section('breadcrumb')
    <li class="breadcrumb-item">Support</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.support.requests.request-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.support.requests.modals.request-form-modal')
        @livewire('admin.support.requests.modals.request-view-modal')
        @livewire('admin.support.requests.modals.request-edit-modal')
        @livewire('admin.support.requests.modals.request-approve-modal')
        @livewire('admin.support.requests.modals.request-complete-modal')
        @livewire('admin.support.requests.modals.request-cancel-modal')
        @livewire('admin.support.requests.modals.request-delete-modal')
        @livewire('admin.support.requests.modals.request-mass-delete-modal')

        {{-- The invoice tracker a row's Payments action opens: the request's
             bill, its receipts, and the approval of a waiting portal payment. --}}
        @livewire('admin.support.requests.modals.request-payments-modal')

        {{-- The bill form a row's Create Invoice action opens: the same dialog
             the Invoices page raises a bill on, and the Products form its own
             "add an item" link opens beside it. --}}
        @livewire('admin.collections.invoices.modals.invoice-form-modal')
        @livewire('admin.collections.products.modals.product-form-modal')
    </div>
@endsection
