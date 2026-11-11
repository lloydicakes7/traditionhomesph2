<section class="d-flex align-items-center text-white"
    style="min-height: calc(100vh - 3.5rem);
           background-image: linear-gradient(180deg, rgba(15, 26, 48, .88) 0%, rgba(15, 26, 48, .62) 55%, rgba(15, 26, 48, .82) 100%), url('{{ asset('image/hero.png') }}');
           background-size: cover;
           background-position: center;
           background-repeat: no-repeat;">
    <div class="container py-5">
        <div class="row">
            <div class="col-12 col-lg-8">
                <p class="text-uppercase small font-weight-bold mb-2" style="letter-spacing: .16em; opacity: .7;">
                    {{ $hoa_short_name ?? config('thha.hoa_short_name') }} &middot; Official Website
                </p>

                <h1 class="mb-3">{{ $hoa_name ?? config('thha.hoa_name') }}</h1>

                <p class="lead mb-4" style="opacity: .9;">
                    {{ $hoa_description ?? config('thha.hoa_description') }}
                </p>

                <a href="#announcements" class="btn btn-primary btn-lg mr-2 mb-2">
                    <i class="fas fa-fw fa-bullhorn mr-1"></i>Latest Announcements
                </a>
                <a href="#location" class="btn btn-outline-secondary btn-lg mb-2">
                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>Find Us
                </a>
            </div>
        </div>
    </div>
</section>
