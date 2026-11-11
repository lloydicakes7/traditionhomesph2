@extends('adminlte::auth.register')

@section('title', __('auth.office_register_title'))
@section('dashboard_url', route('home'))
@section('register_url', route('admin.register.store'))
@section('login_url', route('login'))
