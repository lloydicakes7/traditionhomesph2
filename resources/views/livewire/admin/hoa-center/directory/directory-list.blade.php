<div>
    {{-- =================================================================== --}}
    {{-- FILTERS + ADD BUTTON                                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <x-adminlte-input name="search" placeholder="Search directory..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-md-3 mb-2 mb-sm-0">
                <x-adminlte-select name="groupFilter" wire:model="groupFilter" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All groups</option>
                    @foreach ($groupFilterOptions as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->entries_count }})</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            @can('create', \App\Models\Directory\DirectoryEntry::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New Entry" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#directoryFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- GROUPED CARD LIST                                                   --}}
    {{-- =================================================================== --}}
    @forelse ($groups as $group)
        <div class="mb-4" wire:key="directory-group-{{ $group->id }}">
            <div class="card shadow-sm mb-0">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0 flex-grow-0">
                        <i class="fas fa-fw fa-layer-group mr-1"></i>{{ $group->name }}
                    </h3>
                    <span class="badge badge-secondary ml-auto">{{ $group->matching_entries_count }}</span>
                </div>
            </div>

            {{-- The group's cards, outside the title card --}}
            <div class="row mt-3">
                @foreach ($group->visible_entries as $entry)
                    <div class="col-12 col-sm-6 col-lg-4 mb-3" wire:key="directory-entry-{{ $entry->id }}">
                        <div class="card h-100 mb-0 shadow-sm">
                            {{-- Flex column + mt-auto on the actions: cards of different
                                 heights still keep their buttons pinned to the bottom --}}
                            <div class="card-body d-flex flex-column">
                                <div class="font-weight-bold">{{ $entry->name }}</div>
                                @if ($entry->role)
                                    <small class="text-muted">{{ $entry->role }}</small>
                                @endif

                                {{-- Contact: phones + email --}}
                                <div class="mt-2">
                                    @forelse ($entry->phones as $phone)
                                        <div class="d-flex align-items-center mb-1">
                                            @if ($phone->label_display)
                                                <span class="badge badge-light border mr-1" style="font-size:0.65rem;">
                                                    {{ $phone->label_display }}
                                                </span>
                                            @endif
                                            <a href="tel:{{ $phone->dialable }}" class="small">{{ $phone->number }}</a>
                                        </div>
                                    @empty
                                        <span class="text-muted small">No number on file</span>
                                    @endforelse

                                    @if ($entry->email)
                                        <div class="mt-1">
                                            <a href="mailto:{{ $entry->email }}" class="small text-muted text-truncate d-inline-block"
                                                style="max-width:100%;">
                                                <i class="fas fa-fw fa-envelope mr-1"></i>{{ $entry->email }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                {{-- Note --}}
                                @if ($entry->note)
                                    <small class="text-muted d-block mt-2">{{ \Illuminate\Support\Str::limit($entry->note, 120) }}</small>
                                @endif

                                {{-- Actions — inside the body, so no footer divider --}}
                                <div class="d-flex justify-content-end mt-auto pt-3">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                        @can('update', \App\Models\Directory\DirectoryEntry::class)
                                            <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                                data-toggle="modal" data-target="#directoryFormModal" data-id="{{ $entry->id }}"
                                                class="btn-xs" title="Edit" />
                                        @endcan
                                        @can('delete', \App\Models\Directory\DirectoryEntry::class)
                                            <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                                data-toggle="modal" data-target="#directoryDeleteModal" data-id="{{ $entry->id }}"
                                                class="btn-xs" title="Delete" />
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Show more / Show less --}}
            @if ($group->hidden_entries_count > 0 || isset($expanded[$group->id]))
                <div class="text-center">
                    @if (isset($expanded[$group->id]))
                        <x-adminlte-button label="Show less" theme="link" icon="fas fa-fw fa-chevron-up"
                            wire:click="showLess({{ $group->id }})" class="btn-sm" />
                    @else
                        <x-adminlte-button label="Show more ({{ $group->hidden_entries_count }} more)"
                            theme="link" icon="fas fa-fw fa-chevron-down"
                            wire:click="showMore({{ $group->id }})" class="btn-sm" />
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-fw fa-address-book fa-2x mb-2"></i>
                @if ($groupFilter !== '')
                    <p class="mb-0">No entries in this group yet.</p>
                @else
                    <p class="mb-0">No directory entries found.</p>
                @endif
            </div>
        </div>
    @endforelse

    {{-- Group pagination --}}
    @if ($groups->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $groups->links() }}
        </div>
    @endif
</div>
