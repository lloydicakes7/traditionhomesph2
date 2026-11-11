@extends('layouts.public')

@section('title', 'Announcements | ' . ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.partials.page-header', [
        'title'       => 'Announcements',
        'icon'        => 'fas fa-fw fa-bullhorn',
        'description' => 'Every notice and advisory the association\'s officers have published.',
    ])

    <section class="pb-5" id="announcements">
        <div class="container">
            @livewire('public.announcements.announcement-list')
        </div>
    </section>

    @livewire('public.announcements.announcement-view-modal')
@endsection
