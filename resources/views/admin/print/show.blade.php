@extends('layouts.admin')

@section('title', $document->label())

@section('breadcrumb')
    <li class="breadcrumb-item">Print Documents</li>
@endsection

@section('page-content')
    {{-- Livewire derives a component's name from its class name, so the
         PrintDocuments namespace is kebab-cased to "print-documents". --}}
    @livewire('admin.print-documents.document-preview', ['document' => $document->value])

    @if ($document->hasBlankForm())
        @can('create', \App\Models\User::class)
            @livewire('admin.homeowners.homeowner.modals.homeowner-form-modal')
        @endcan
    @endif

    {{-- The Membership Form adds someone to the member's own household through
         the shared members dialog, so the page carries that dialog too. --}}
    @if ($document->value === \App\Enums\PrintDocument::MEMBERSHIP_FORM)
        @can('update', \App\Models\Household\Household::class)
            @livewire('admin.modals.edit-members-modal')
        @endcan
    @endif
@endsection
