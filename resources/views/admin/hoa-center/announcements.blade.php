@extends('layouts.admin')

@section('title', 'Announcements')

@section('breadcrumb')
    <li class="breadcrumb-item">HOA Center</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.hoa-center.announcements.announcement-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.hoa-center.announcements.modals.announcement-form-modal')
        @livewire('admin.hoa-center.announcements.modals.announcement-delete-modal')
    </div>
@endsection
