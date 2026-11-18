@extends('layouts.admin')

@section('title', 'Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item">Finance</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.expenses.expense-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.expenses.modals.expense-view-modal')
        @livewire('admin.expenses.modals.expense-wizard-modal')
        @livewire('admin.expenses.modals.expense-delete-modal')
        @livewire('admin.expenses.modals.expense-mass-delete-modal')
        @livewire('admin.accounting.journal-entries.modals.post-to-journal-modal')
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/select2.js') }}"></script>
@endpush
