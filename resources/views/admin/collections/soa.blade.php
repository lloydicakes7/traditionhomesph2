@extends('layouts.admin')

@section('title', 'Email SOA')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.soa.soa-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.soa.modals.soa-view-modal')
        @livewire('admin.collections.soa.modals.soa-delete-modal')
        @livewire('admin.collections.soa.modals.soa-mass-delete-modal')
    </div>
@endsection
