@extends('layouts.admin')

@section('title', 'Transfers')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.transfers.transfer-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.accounting.transfers.modals.transfer-form-modal')
        @livewire('admin.accounting.transfers.modals.transfer-delete-modal')

        {{-- The journal view is shared with the journal entries page, so a
             transfer's posting can be read without leaving the register. --}}
        @livewire('admin.accounting.journal-entries.modals.journal-entry-view-modal')
    </div>
@endsection
