@php
    $portalFeatures = [
        'Your statement of account, with every due and payment in one running balance.',
        'File requests and concerns, and follow each one through to resolution.',
        'Keep your household and contact details current from your own account.',
        'Read every announcement as soon as the officers publish it.',
    ];
@endphp

<section class="py-5" id="portal-features">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                <p class="text-uppercase small font-weight-bold mb-2" style="letter-spacing: .16em;">
                    Homeowner Portal
                </p>

                <h2 class="h3 mb-3">Your household, in your pocket.</h2>

                <p class="text-muted mb-4" style="line-height: 1.7;">
                    Every household account is issued by the HOA office, and every account gets
                    the same tools. Sign in to see the whole of your record with the association.
                </p>

                <ul class="list-unstyled mb-4">
                    @foreach ($portalFeatures as $feature)
                        <li class="d-flex {{ $loop->last ? '' : 'mb-3' }}">
                            <i class="fas fa-fw fa-check-circle text-primary mt-1 mr-2"></i>
                            <span style="line-height: 1.7;">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ auth('homeowner')->check() ? route('portal.dashboard') : route('login') }}"
                    class="btn btn-primary btn-lg">
                    <i class="fas fa-fw fa-sign-in-alt mr-1"></i>Login
                </a>
            </div>

            <div class="col-12 col-lg-6">
                <div class="mx-auto" style="max-width: 280px;">
                    <div class="card mb-0 elevation-3 overflow-hidden border-secondary rounded-lg" style="border-width: 4px;">
                        <img src="{{ asset('image/portal-preview.png') }}"
                            class="img-fluid w-100" alt="The homeowner portal as it appears on a phone">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
