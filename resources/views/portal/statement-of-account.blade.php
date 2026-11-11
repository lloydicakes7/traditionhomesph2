@extends('layouts.portal')

@section('title', 'Statement of Account')

@section('page-content')
    <div>
        @livewire('portal.statements.statement-list')
    </div>
@endsection
