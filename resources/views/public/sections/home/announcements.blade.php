<section class="py-5 bg-light" id="announcements">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-1">
                <i class="fas fa-fw fa-bullhorn text-primary mr-1"></i>Announcements
            </h2>
            <p class="text-muted mb-0">Notices and advisories published by the association's officers.</p>
        </div>

        <x-ui.cards.announcement-cards :announcements="$announcements" />

        @if ($hasMoreAnnouncements)
            <div class="text-center">
                <a href="{{ route('announcements') }}" class="btn btn-outline-primary">
                    View all announcements <i class="fas fa-fw fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>

    @livewire('public.announcements.announcement-view-modal')
</section>
