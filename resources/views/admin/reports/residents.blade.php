@extends('layouts.admin')

@section('title', 'Residents Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.resident-report')
@endsection
