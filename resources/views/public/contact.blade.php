@extends('layouts.public')

@section('title', __('public.contact.title') . ' | ' . ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.partials.page-header', [
        'title'       => __('public.contact.heading'),
        'icon'        => 'fas fa-fw fa-address-card',
        'description' => __('public.contact.description'),
    ])

    {{-- ===== Map ===== --}}

    <section class="pb-4" id="contact-map">
        <div class="container">
            <h2 class="h4 mb-3">
                <i class="fas fa-fw fa-map-marker-alt text-primary mr-1"></i>{{ __('public.contact.map_title') }}
            </h2>

            <div class="card mb-0 overflow-hidden">
                <div class="embed-responsive embed-responsive-21by9">
                    <iframe class="embed-responsive-item" src="{{ $map['embed_url'] }}"
                        title="Map of {{ $map['query'] }}" loading="lazy" allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <div class="text-right mt-3">
                <a href="{{ $map['directions_url'] }}" target="_blank" rel="noopener"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-fw fa-directions mr-1"></i>{{ __('public.contact.directions') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ===== Office details ===== --}}

    <section class="pb-5" id="contact-details">
        <div class="container">
            <div class="row">
                @forelse ($channels as $channel)
                    <div class="col-12 col-sm-6 col-lg-3 d-flex mb-3">
                        <div class="card h-100 w-100 mb-0">
                            <div class="card-body">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 bg-navy-soft text-info"
                                    style="width: 44px; height: 44px;">
                                    <i class="{{ $channel['icon'] }}"></i>
                                </span>

                                <h3 class="h6 font-weight-bold mb-1">{{ $channel['label'] }}</h3>

                                @if (! empty($channel['href']))
                                    <a href="{{ $channel['href'] }}" class="text-muted small">{{ $channel['value'] }}</a>
                                @else
                                    <p class="text-muted small mb-0">{{ $channel['value'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <x-adminlte-alert theme="info" icon="fas fa-fw fa-info-circle">
                            {{ __('public.contact.no_details') }}
                        </x-adminlte-alert>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ===== Message ===== --}}

    <section class="pb-5 bg-light" id="contact-message">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <div class="card mb-0">
                        <div class="card-body">
                            <h2 class="h4 mb-1">{{ __('public.contact.form_title') }}</h2>
                            <p class="text-muted">{{ __('public.contact.form_description') }}</p>

                            @livewire('public.contact.contact-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
