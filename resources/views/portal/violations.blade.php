@extends('layouts.portal')

@section('title', 'Violations')

@section('page-content')
    <div>
        @livewire('portal.violations.violation-list')

        {{-- The notice itself (event-driven sibling) --}}
        @livewire('portal.violations.modals.violation-view-modal')
    </div>
@endsection
