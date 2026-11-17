@extends('layouts.portal')

@section('title', __('households.link.title'))

@section('page-content')
    <div>
        @livewire('portal.household-property.household-link-request-list')
    </div>
@endsection
