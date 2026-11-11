@extends('layouts.public')

@section('title', 'Members | ' . ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.partials.page-header', [
        'title'       => 'HOA Officers',
        'icon'        => 'fas fa-fw fa-user-tie',
        'description' => 'The officers and staff serving the association.',
    ])

    <section class="pb-5" id="members">
        <div class="container">
            @livewire('public.members.member-list')
        </div>
    </section>
@endsection
