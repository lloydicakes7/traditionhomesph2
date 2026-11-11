@extends('layouts.admin')

@section('title', 'Public Pages')

@section('plugins.Summernote', true)

@section('breadcrumb')
    <li class="breadcrumb-item">HOA Center</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.hoa-center.contents.content-list')

        @livewire('admin.hoa-center.contents.modals.content-form-modal')
        @livewire('admin.hoa-center.contents.modals.content-delete-modal')
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/rich-text.js') }}"></script>
@endpush
