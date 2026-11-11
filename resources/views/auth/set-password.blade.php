@extends('adminlte::auth.passwords.reset')

@section('title', __('auth.set_password_title'))
@section('dashboard_url', route('home'))
@section('password_reset_url', route('password.set.update'))
