@extends('layouts.admin')

@section('title', 'Profit & Loss Statement')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.profit-and-loss')
@endsection
