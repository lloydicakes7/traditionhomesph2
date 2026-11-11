@extends('layouts.admin')

@section('title', 'Journal Entries')

@section('breadcrumb')
    <li class="breadcrumb-item">Accounting</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.accounting.journal-entries.journal-entry-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.accounting.journal-entries.modals.journal-entry-view-modal')
        @livewire('admin.accounting.journal-entries.modals.journal-entry-form-modal')
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/journal-entry-lines.js') }}"></script>
@endpush
