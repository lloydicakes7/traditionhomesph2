@extends('layouts.admin')

@section('title', 'Authentication')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    @livewire('admin.settings.authentication-settings')
@endsection
