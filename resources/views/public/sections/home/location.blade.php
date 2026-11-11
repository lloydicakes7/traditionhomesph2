<section class="py-5" id="location">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h3 mb-1">
                <i class="fas fa-fw fa-map-marker-alt text-primary mr-1"></i>Location
            </h2>
            <p class="text-muted mb-0">Visit the HOA office, or find your way to the subdivision.</p>
        </div>

        <div class="row">
            <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                <div class="card h-100 mb-0">
                    <div class="card-body">
                        <h3 class="font-weight-bold mb-3">Association Address</h3>

                        @if (!empty($hoa_address))
                            <p class="mb-3 text-primary">
                                <i class="fas fa-fw fa-map-marker-alt mr-1"></i>
                                {{ $hoa_address }}
                            </p>
                        @else
                            <p class="text-muted mb-3">
                                The association's address has not been posted yet.
                            </p>
                        @endif

                        @if (!empty($contact_number))
                            <p class="mb-2">
                                <i class="fas fa-fw fa-phone-alt text-primary mr-1"></i>
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact_number) }}">{{ $contact_number }}</a>
                            </p>
                        @endif

                        @if (! empty($email))
                            <p class="mb-2">
                                <i class="fas fa-fw fa-envelope text-primary mr-1"></i>
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            </p>
                        @endif

                        @if (! empty($website))
                            <p class="mb-2">
                                <i class="fas fa-fw fa-globe text-primary mr-1"></i>
                                <a href="{{ $website }}" target="_blank" rel="noopener">{{ $website }}</a>
                            </p>
                        @endif

                        <a href="{{ $map['directions_url'] }}" target="_blank" rel="noopener"
                            class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-fw fa-directions mr-1"></i>Get Directions
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="card h-100 mb-0 overflow-hidden">
                    <div class="embed-responsive embed-responsive-16by9 h-100">
                        <iframe class="embed-responsive-item" src="{{ $map['embed_url'] }}"
                            title="Map of {{ $map['query'] }}" loading="lazy" allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
