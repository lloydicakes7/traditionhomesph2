@extends('layouts.admin')

@section('title', 'HOA Members')

@section('breadcrumb')
    <li class="breadcrumb-item">HOA Center</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.hoa-center.members.member-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.hoa-center.members.modals.member-form-modal')
        @livewire('admin.hoa-center.members.modals.member-delete-modal')
    </div>
@endsection
