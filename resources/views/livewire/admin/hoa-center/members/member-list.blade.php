<div>
    {{-- =================================================================== --}}
    {{-- FILTERS + ADD BUTTON                                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search members..." wire:model.debounce.500ms="search"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @can('create', \App\Models\HoaMember::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New Member" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#memberFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MEMBER CARDS                                                        --}}
    {{-- =================================================================== --}}
    <div class="row">
        @forelse ($members as $member)
            <div class="col-12 col-sm-6 col-lg-4 mb-3" wire:key="hoa-member-{{ $member->id }}">
                <div class="card h-100 mb-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <img src="{{ $member->photo_url ?: asset('image/user.png') }}" alt="{{ $member->name }}"
                                class="rounded-circle elevation-1 mr-3"
                                style="width:64px; height:64px; object-fit:cover;">
                            <div class="mr-2">
                                <div class="font-weight-bold">{{ $member->name }}</div>
                                <small class="text-muted d-block">{{ $member->position }}</small>
                            </div>
                        </div>

                        <div class="mt-2">
                            @if ($member->phone)
                                <div class="small mb-1">
                                    <i class="fas fa-fw fa-phone-alt mr-1"></i>
                                    <a href="tel:{{ $member->dialable_phone }}">{{ $member->phone }}</a>
                                </div>
                            @endif

                            @if ($member->email)
                                <div class="small">
                                    <i class="fas fa-fw fa-envelope mr-1"></i>
                                    <a href="mailto:{{ $member->email }}" class="text-muted text-truncate d-inline-block"
                                        style="max-width:100%;">{{ $member->email }}</a>
                                </div>
                            @endif

                            @unless ($member->phone || $member->email)
                                <span class="text-muted small">No contact details on file</span>
                            @endunless
                        </div>

                        {{-- Note --}}
                        @if ($member->note)
                            <small class="text-muted d-block mt-2">{{ \Illuminate\Support\Str::limit($member->note, 120) }}</small>
                        @endif

                        <div class="d-flex justify-content-end mt-auto pt-3">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                @can('update', \App\Models\HoaMember::class)
                                    <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                        data-toggle="modal" data-target="#memberFormModal" data-id="{{ $member->id }}"
                                        class="btn-xs" title="Edit" />
                                @endcan
                                @can('delete', \App\Models\HoaMember::class)
                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                        data-toggle="modal" data-target="#memberDeleteModal" data-id="{{ $member->id }}"
                                        class="btn-xs" title="Delete" />
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
                        <i class="fas fa-fw fa-user-friends fa-2x mb-2"></i>
                        @if ($search !== '')
                            <p class="mb-0">No members match your search.</p>
                        @else
                            <p class="mb-0">No HOA members listed yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($members->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $members->links() }}
        </div>
    @endif
</div>
