@extends('layouts.admin')

@section('title', 'FAQs')

@section('breadcrumb')
    <li class="breadcrumb-item">HOA Center</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.hoa-center.faqs.faq-list')

        @livewire('admin.hoa-center.faqs.modals.faq-form-modal')
        @livewire('admin.hoa-center.faqs.modals.faq-delete-modal')
    </div>
@endsection
