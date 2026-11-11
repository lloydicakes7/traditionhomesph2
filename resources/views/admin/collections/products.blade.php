@extends('layouts.admin')

@section('title', 'Products and Services')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.products.product-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.products.modals.product-form-modal')
        @livewire('admin.collections.products.modals.product-delete-modal')
        @livewire('admin.collections.products.modals.product-mass-delete-modal')
    </div>
@endsection
