@extends('layouts.admin')

@section('title', 'Backup')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    @livewire('admin.settings.backup-settings')
@endsection
