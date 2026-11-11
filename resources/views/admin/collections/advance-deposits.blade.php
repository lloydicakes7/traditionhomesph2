@extends('layouts.admin')

@section('title', 'Advance Deposits')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.advance-deposits.advance-deposit-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-form-modal')
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-apply-modal')
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-view-modal')
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-delete-modal')
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-refund-modal')
        @livewire('admin.collections.advance-deposits.modals.advance-deposit-mass-delete-modal')
    </div>
@endsection
