@extends('layouts.admin')

@section('title', 'Statement of Account')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.statement-of-account')
@endsection
