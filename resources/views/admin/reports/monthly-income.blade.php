@extends('layouts.admin')

@section('title', 'Monthly Income Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.monthly-income')
@endsection
