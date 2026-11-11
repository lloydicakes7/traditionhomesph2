@extends('layouts.admin')

@section('title', 'Supplier')

@section('breadcrumb')
    <li class="breadcrumb-item">Finance</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.expenses.supplier-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.expenses.modals.supplier-form-modal')
        @livewire('admin.expenses.modals.supplier-delete-modal')
        @livewire('admin.expenses.modals.supplier-mass-delete-modal')
    </div>
@endsection
