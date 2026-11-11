@extends('layouts.admin')

@section('title', 'General')

@section('breadcrumb')
    <li class="breadcrumb-item">Settings</li>
@endsection

@section('page-content')
    @livewire('admin.settings.general-settings')
@endsection
