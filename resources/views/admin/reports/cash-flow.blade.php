@extends('layouts.admin')

@section('title', 'Cash Flow Statement')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.cash-flow')
@endsection
