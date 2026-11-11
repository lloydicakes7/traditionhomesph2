@extends('layouts.admin')

@section('title', 'Daily Expenses Report')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.daily-expenses')
@endsection
