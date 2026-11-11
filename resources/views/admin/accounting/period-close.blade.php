@extends('layouts.admin')

@section('title', 'Period Close')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.period-close.period-close-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.accounting.period-close.modals.period-close-modal')
        @livewire('admin.accounting.period-close.modals.period-reopen-modal')
    </div>
@endsection
