@extends('layouts.admin')

@section('title', 'Amenities Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.amenity-report')
@endsection
