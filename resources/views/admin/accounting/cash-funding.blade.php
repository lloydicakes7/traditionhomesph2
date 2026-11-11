@extends('layouts.admin')

@section('title', 'Cash Funding')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.cash-funding.cash-funding-table-list')
        @livewire('admin.accounting.cash-funding.modals.cash-funding-form-modal')
        @livewire('admin.accounting.journal-entries.modals.journal-entry-view-modal')
    </div>
@endsection
