@extends('layouts.admin')

@section('title', 'Chart of Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.chart-of-accounts.account-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.accounting.chart-of-accounts.modals.account-form-modal')
        @livewire('admin.accounting.chart-of-accounts.modals.account-delete-modal')
    </div>
@endsection
