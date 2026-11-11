<div class="bg-secondary text-white py-1" style="font-size: 0.8125rem;">
    <div class="container d-flex flex-column flex-md-row justify-content-md-between align-items-center text-center text-md-left">

        <p class="mb-1 mb-md-0">
            <i class="far fa-fw fa-clock mr-1" aria-hidden="true"></i><span data-thha-clock>&mdash;</span>
        </p>

        <p class="mb-0 d-flex flex-wrap justify-content-center align-items-center">
            @if (! empty($contact['website']))
                <a class="text-white mr-3" href="{{ $contact['website'] }}" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-fw fa-globe mr-1" aria-hidden="true"></i>{{ $contact['website'] }}
                </a>
            @endif

            @if (! empty($contact['contact_number']))
                <a class="text-white" href="tel:{{ preg_replace('/[^\d+]/', '', $contact['contact_number']) }}">
                    <i class="fas fa-fw fa-phone-alt mr-1" aria-hidden="true"></i>{{ $contact['contact_number'] }}
                </a>
            @endif
        </p>

    </div>
</div>