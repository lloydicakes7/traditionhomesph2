@extends('layouts.admin')

@section('title', 'Print')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    @livewire('admin.settings.print-setting-form')
@endsection
