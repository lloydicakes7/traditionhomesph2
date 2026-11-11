@extends('layouts.portal')

@section('title', __('rbi.hub.my_rbi'))

@section('page-content')
    <div>
        @livewire('portal.rbi.my-rbi')
    </div>
@endsection
