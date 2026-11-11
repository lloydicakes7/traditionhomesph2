<div>
    {{-- =================================================================== --}}
    {{-- FILTERS + ADD BUTTON                                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search announcements..." wire:model.debounce.500ms="search"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @can('create', \App\Models\Announcement::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New Announcement" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#announcementFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- ANNOUNCEMENT CARDS                                                  --}}
    {{-- =================================================================== --}}
    <div class="row">
        @forelse ($announcements as $announcement)
            <div class="col-12 col-sm-6 col-lg-4 mb-3 d-flex" wire:key="announcement-{{ $announcement->id }}">
                <div class="card h-100 mb-0 shadow-sm w-100">
                    <img src="{{ $announcement->image_url ?: asset('image/placeholder.jpg') }}"
                        alt="{{ $announcement->title }}" class="card-img-top"
                        style="height:170px; object-fit:cover;">

                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title font-weight-bold mb-1">{{ $announcement->title }}</h6>
                        <small class="text-muted d-block mb-2">
                            <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $announcement->display_date }}
                        </small>

                        <p class="card-text text-muted small mb-0">
                            {{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 160) }}
                        </p>

                        <div class="d-flex justify-content-end mt-auto pt-3">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                @can('update', \App\Models\Announcement::class)
                                    <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                        data-toggle="modal" data-target="#announcementFormModal"
                                        data-id="{{ $announcement->id }}" class="btn-xs" title="Edit" />
                                @endcan
                                @can('delete', \App\Models\Announcement::class)
                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                        data-toggle="modal" data-target="#announcementDeleteModal"
                                        data-id="{{ $announcement->id }}" class="btn-xs" title="Delete" />
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-fw fa-bullhorn fa-2x mb-2"></i>
                        @if ($search !== '')
                            <p class="mb-0">No announcements match your search.</p>
                        @else
                            <p class="mb-0">No announcements posted yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($announcements->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $announcements->links() }}
        </div>
    @endif
</div>
