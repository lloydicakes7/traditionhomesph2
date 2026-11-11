@php
    $features = [
        [
            'icon'  => 'fas fa-fw fa-bullhorn',
            'tile'  => 'bg-terracotta-soft text-primary',
            'title' => 'Announcements',
            'text'  => 'Notices and advisories from the board, published the moment the officers post them.',
        ],
        [
            'icon'  => 'fas fa-fw fa-user-tie',
            'tile'  => 'bg-navy-soft text-info',
            'title' => 'Officers Directory',
            'text'  => 'The officers and staff serving the association, each with a way to reach them.',
        ],
        [
            'icon'  => 'fas fa-fw fa-sign-in-alt',
            'tile'  => 'bg-green-soft text-success',
            'title' => 'Homeowner Portal',
            'text'  => 'Your statement of account, requests and household details, all behind one login.',
        ],
        [
            'icon'  => 'fas fa-fw fa-file-invoice-dollar',
            'tile'  => 'bg-terracotta-soft text-primary',
            'title' => 'Billing & Payments',
            'text'  => 'Dues, balances and receipts kept in one running statement you can check any time.',
        ],
        [
            'icon'  => 'fas fa-fw fa-clipboard-list',
            'tile'  => 'bg-navy-soft text-info',
            'title' => 'Requests & Concerns',
            'text'  => 'File it once, follow it to resolution, and keep the reference number for every ticket.',
        ],
        [
            'icon'  => 'fas fa-fw fa-map-marker-alt',
            'tile'  => 'bg-green-soft text-success',
            'title' => 'Location & Contact',
            'text'  => 'Directions to the subdivision, and the office details, kept current by the board.',
        ],
    ];
@endphp

<section class="py-5" id="website-features">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-1">
                <i class="fas fa-fw fa-star text-primary mr-1"></i>What This Website Offers
            </h2>
            <p class="text-muted mb-0">Everything the association publishes, and everything a homeowner needs, in one place.</p>
        </div>

        <div class="row">
            @foreach ($features as $feature)
                <div class="col-12 col-md-6 col-lg-4 d-flex mb-4">
                    <div class="card h-100 w-100 mb-0">
                        <div class="card-body">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 {{ $feature['tile'] }}"
                                style="width: 52px; height: 52px;">
                                <i class="{{ $feature['icon'] }}"></i>
                            </span>

                            <h3 class="h6 font-weight-bold mb-2">{{ $feature['title'] }}</h3>
                            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $feature['text'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
