<section class="py-5 bg-light" id="members">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-1">
                <i class="fas fa-fw fa-user-tie text-primary mr-1"></i>HOA Officers
            </h2>
            <p class="text-muted mb-0">The officers and staff serving the association.</p>
        </div>

        <x-ui.cards.member-cards :members="$members" columns="col-12 col-sm-6 col-lg-4" />

        @if ($hasMoreMembers)
            <div class="text-center">
                <a href="{{ route('members') }}" class="btn btn-outline-primary">
                    See all members <i class="fas fa-fw fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>
</section>
