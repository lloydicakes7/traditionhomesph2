@php
    $contact = $contact ?? [];
    $footerLinks = $footerLinks ?? [];

    $logoUrl = \App\Utility\StorageHelper::photoUrl(
        $logo_path ?? '',
        asset(config('adminlte.logo_img', 'image/logo.jpg'))
    );
@endphp

<footer class="main-footer text-white"
    style="background: linear-gradient(180deg, var(--secondary-deep), var(--secondary) 60%, var(--secondary-deep)); border-top: none;">

    <div class="container py-5">
        <div class="row">
            <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $logoUrl }}" alt="{{ $short_name }} logo"
                        class="rounded mr-3" style="width: 48px; height: 48px; object-fit: cover;">

                    <span class="brand-text text-white" style="font-size: 1.3rem;">
                        {{ $short_name }}
                    </span>
                </div>

                @if (! empty($description))
                    <p class="mb-4" style="max-width: 52ch; opacity: .75;">{{ $description }}</p>
                @endif


                @if (! empty($contact['address']) || ! empty($contact['contact_number']) || ! empty($contact['email']) || ! empty($contact['website']))
                    <ul class="list-unstyled small mb-0">
                        @if (! empty($contact['address']))
                            <li class="mb-2">
                                <i class="fas fa-fw fa-map-marker-alt mr-2"></i>{{ $contact['address'] }}
                            </li>
                        @endif

                        @if (! empty($contact['contact_number']))
                            <li class="mb-2">
                                <i class="fas fa-fw fa-phone-alt mr-2"></i>
                                <a class="text-white" href="tel:{{ preg_replace('/[^\d+]/', '', $contact['contact_number']) }}">{{ $contact['contact_number'] }}</a>
                            </li>
                        @endif

                        @if (! empty($contact['email']))
                            <li class="mb-2">
                                <i class="fas fa-fw fa-envelope mr-2"></i>
                                <a class="text-white" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                            </li>
                        @endif

                        @if (! empty($contact['website']))
                            <li>
                                <i class="fas fa-fw fa-globe mr-2"></i>
                                <a class="text-white" href="{{ $contact['website'] }}" target="_blank" rel="noopener noreferrer">{{ $contact['website'] }}</a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

            {{-- RIGHT — quick links, one per line --}}
            <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-4 gap-md-5">
                    @foreach($footerLinks as $links)
                        <div>
                            <h3 class="h6 text-uppercase font-weight-bold mb-3" style="letter-spacing: .1em; opacity: .6;">
                                {{ $links['title'] ?? 'Quick Links' }}
                            </h3>

                            <ul class="list-unstyled mb-0">
                                @foreach ($links['items'] as $link)
                                    <li class="mb-2">
                                        <a class="text-white" href="{{ $link['href'] }}" style="opacity: .85;">
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <hr class="my-4" style="border-top: 1px solid rgba(255, 255, 255, .15);">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
            <span style="opacity: .7;">
                &copy; {{ date('Y') }} {{ $short_name }}. All rights reserved.
            </span>

            <span class="mt-2 mt-md-0" style="opacity: .7;">
                {{ $short_name }} Official Website
            </span>
        </div>
    </div>
</footer>
