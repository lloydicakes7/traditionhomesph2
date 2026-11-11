@extends('layouts.admin')

@section('title', 'Monthly Dues Table View')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    @livewire('admin.collections.monthly-dues.monthly-due-table-matrix')
@endsection
