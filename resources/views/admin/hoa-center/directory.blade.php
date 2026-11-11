@extends('layouts.admin')

@section('title', 'Directory')

@section('breadcrumb')
    <li class="breadcrumb-item">HOA Center</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.hoa-center.directory.directory-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.hoa-center.directory.modals.directory-form-modal')
        @livewire('admin.hoa-center.directory.modals.directory-delete-modal')
    </div>
@endsection
