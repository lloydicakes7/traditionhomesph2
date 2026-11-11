@extends('layouts.admin')

@section('title', 'Community Reports')

@section('breadcrumb')
    <li class="breadcrumb-item">Reports</li>
@endsection

@section('page-content')
    @livewire('admin.reports.community')
@endsection
