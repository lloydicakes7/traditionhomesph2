@extends('layouts.public')

@section('title', __('public.services.title') . ' | ' . ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.partials.page-header', [
        'title'       => __('public.services.heading'),
        'icon'        => 'fas fa-fw fa-concierge-bell',
        'description' => __('public.services.description'),
    ])

    {{-- ===== Documents ===== --}}

    <section class="pb-4" id="services">
        <div class="container">
            <div class="row">
                @foreach ($documents as $document)
                    <div class="col-12 col-md-6 col-lg-4 d-flex mb-4">
                        <div class="card h-100 w-100 mb-0">
                            <div class="card-body d-flex flex-column">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 bg-terracotta-soft text-primary"
                                    style="width: 52px; height: 52px;">
                                    <i class="{{ $document['icon'] }}"></i>
                                </span>

                                <h2 class="h6 font-weight-bold mb-2">{{ $document['label'] }}</h2>
                                <p class="text-muted small mb-4" style="line-height: 1.7;">
                                    {{ $document['description'] }}
                                </p>

                                <a href="{{ route('portal.requests') }}" class="btn btn-primary btn-sm mt-auto">
                                    <i class="fas fa-fw fa-paper-plane mr-1"></i>{{ __('public.services.request') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Steps ===== --}}

    <section class="pb-5 bg-light" id="service-steps">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <h2 class="h4 mb-3">
                        <i class="fas fa-fw fa-list-ol text-primary mr-1"></i>{{ __('public.services.steps_title') }}
                    </h2>

                    <ol class="pl-4 mb-3">
                        @foreach ($steps as $step)
                            <li class="mb-2" style="line-height: 1.7;">{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
@endsection
