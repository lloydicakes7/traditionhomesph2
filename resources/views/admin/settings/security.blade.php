@extends('layouts.admin')

@section('title', 'Security')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    @livewire('admin.settings.security-settings')
@endsection
