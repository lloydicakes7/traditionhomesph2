@extends('layouts.admin')

@section('title', 'Monthly Dues Generator')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    @livewire('admin.collections.monthly-dues.monthly-dues-generator')
@endsection
