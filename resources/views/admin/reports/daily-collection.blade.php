@extends('layouts.admin')

@section('title', 'Daily Collection Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.daily-collection')
@endsection
