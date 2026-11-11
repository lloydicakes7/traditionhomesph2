@extends('layouts.admin')

@section('title', 'AR Aging Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.ar-aging')
@endsection
