@extends('layouts.admin')

@section('title', 'Violations')

@section('breadcrumb')
    <li class="breadcrumb-item">Support</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.support.violations.violation-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.support.violations.modals.violation-form-modal')
        @livewire('admin.support.violations.modals.violation-view-modal')
        @livewire('admin.support.violations.modals.violation-resolve-modal')
        @livewire('admin.support.violations.modals.violation-delete-modal')
        @livewire('admin.support.violations.modals.violation-mass-delete-modal')
    </div>
@endsection
