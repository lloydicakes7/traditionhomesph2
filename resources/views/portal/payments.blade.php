@extends('layouts.portal')

@section('title', 'My Payments')

@section('page-content')
    <div>
        @livewire('portal.payments.payment-list')
    </div>
@endsection
