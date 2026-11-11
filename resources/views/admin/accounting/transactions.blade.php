@extends('layouts.admin')

@section('title', 'Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.transactions.transaction-register')

        {{-- The journal view is shared with the journal entries page, so a
             movement can be opened to read the whole posting behind it. --}}
        @livewire('admin.accounting.journal-entries.modals.journal-entry-view-modal')
    </div>
@endsection
