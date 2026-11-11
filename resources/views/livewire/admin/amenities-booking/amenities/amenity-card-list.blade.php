<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + ADD BUTTON                                                 --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search amenities..." wire:model.debounce.500ms="search"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @can('create', \App\Models\Amenity\Amenity::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="Add Amenity" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#amenityFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- AMENITY CARDS                                                       --}}
    {{-- =================================================================== --}}
    <div class="row">
        @forelse ($amenities as $amenity)
            <div class="col-12 col-sm-6 col-lg-4 mb-3 d-flex" wire:key="amenity-{{ $amenity->id }}">
                <div class="card h-100 mb-0 shadow-sm w-100">
                    {{-- Photo, with the rate type pinned to it --}}
                    <div class="position-relative">
                        <img src="{{ $amenity->image_url ?: asset('image/placeholder.jpg') }}"
                            alt="{{ $amenity->name }}" class="card-img-top"
                            style="height:150px; object-fit:cover;">

                        <span class="badge bg-light text-dark position-absolute"
                            style="top:0.75rem; left:0.75rem;">
                            {{ $amenity->rate_type_enum?->label() ?? '—' }}
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        {{-- Name, rate and the week it is open --}}
                        <h6 class="card-title font-weight-bold mb-1">{{ $amenity->name }}</h6>
                        <div class="text-success font-weight-bold mb-1">{{ $amenity->rate_display }}</div>
                        <small class="text-muted d-block mb-0">{{ $amenity->schedule_summary }}</small>

                        {{-- Actions — inside the body and pinned to the bottom right,
                             the icon buttons the HOA Center's cards carry --}}
                        <div class="d-flex justify-content-end mt-auto pt-3">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                    data-toggle="modal" data-target="#amenityViewModal" data-id="{{ $amenity->id }}"
                                    class="btn-xs" title="View Amenity" />

                                @can('update', \App\Models\Amenity\Amenity::class)
                                    <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                        data-toggle="modal" data-target="#amenityFormModal" data-id="{{ $amenity->id }}"
                                        class="btn-xs" title="Edit Amenity" />
                                @endcan

                                @can('delete', \App\Models\Amenity\Amenity::class)
                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                        data-toggle="modal" data-target="#amenityDeleteModal" data-id="{{ $amenity->id }}"
                                        class="btn-xs" title="Delete Amenity" />
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
                        <i class="far fa-fw fa-calendar-check fa-2x mb-2"></i>
                        @if ($search !== '')
                            <p class="mb-0">No amenities match your search.</p>
                        @else
                            <p class="mb-0">No amenities added yet. Add the association's bookable facilities to start taking reservations.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($amenities->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $amenities->links() }}
        </div>
    @endif
</div>
