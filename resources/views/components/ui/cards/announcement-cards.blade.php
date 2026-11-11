@props(['announcements', 'teaserLength' => 180, 'search' => null])

<div class="row">
    @forelse ($announcements as $announcement)
        @php
            $body = trim(strip_tags($announcement->body));
            $teased = \Illuminate\Support\Str::length($body) > $teaserLength;
        @endphp

        <div class="col-12 col-md-6 col-lg-4 d-flex mb-4" wire:key="announcement-card-{{ $announcement->id }}">
            <div class="card h-100 w-100 mb-0">
                <img src="{{ $announcement->image_url ?: asset('image/placeholder.jpg') }}"
                    alt="{{ $announcement->title }}" class="card-img-top"
                    style="height: 170px; object-fit: cover;">

                <div class="card-body d-flex flex-column">
                    <h3 class="h6 card-title font-weight-bold mb-1">{{ $announcement->title }}</h3>

                    <small class="text-muted d-block mb-2">
                        <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $announcement->display_date }}
                    </small>

                    <p class="card-text text-muted small mb-0">
                        {{ \Illuminate\Support\Str::limit($body, $teaserLength) }}
                    </p>

                    @if ($teased)
                        <div class="mt-auto pt-3">
                            <x-adminlte-button label="Read More" icon="fas fa-fw fa-arrow-right" theme="outline-primary"
                                data-toggle="modal" data-target="#announcementViewModal"
                                data-id="{{ $announcement->id }}" class="btn-xs" title="Read" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-fw fa-bullhorn fa-2x mb-2"></i>
                    @if (filled($search))
                        <p class="mb-0">No announcements match your search.</p>
                    @else
                        <p class="mb-0">No announcements have been posted yet.</p>
                    @endif
                </div>
            </div>
        </div>
    @endforelse
</div>
