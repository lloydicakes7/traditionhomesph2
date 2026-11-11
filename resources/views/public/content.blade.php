@extends('layouts.public')

@section('title', $content->title . ' | ' . ($hoa_name ?? config('thha.hoa_name')))

@section('content')
    @include('public.partials.page-header', [
        'title'       => $content->title,
        'icon'        => 'fas fa-fw fa-file-alt',
        'description' => '',
    ])

    <section class="pb-5" id="content-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    @if ($content->image_url)
                        <img src="{{ $content->image_url }}" alt="{{ $content->title }}"
                            class="img-fluid rounded w-100 mb-4 shadow-sm"
                            style="max-height: 380px; object-fit: cover;" loading="lazy">
                    @endif

                    <div class="card mb-0">
                        <div class="card-body" style="line-height: 1.8;">
                            {!! $content->details_html !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
