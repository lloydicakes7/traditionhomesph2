@extends('layouts.admin')

@section('title', 'Expense Categories')

@section('breadcrumb')
    <li class="breadcrumb-item">Finance</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.expenses.category-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.expenses.modals.category-form-modal')
        @livewire('admin.expenses.modals.expense-category-delete-modal')
        @livewire('admin.expenses.modals.expense-category-mass-delete-modal')
    </div>
@endsection
