@extends('adminlte::auth.passwords.confirm')

@section('title', 'Confirm Password')
@section('dashboard_url', route('home'))
@section('password_confirm_url', route('portal.password.confirm'))
@section('password_reset_url', route('password.request'))
