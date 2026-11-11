@extends('layouts.admin')

@section('title', 'Product Categories')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.product-categories.product-category-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.product-categories.modals.product-category-form-modal')
        @livewire('admin.collections.product-categories.modals.product-category-delete-modal')
        @livewire('admin.collections.product-categories.modals.product-category-mass-delete-modal')
    </div>
@endsection
