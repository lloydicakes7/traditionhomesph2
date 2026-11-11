<div>
    {{-- =================================================================== --}}
    {{-- HEADER: SEARCH + CREATE BUTTON                                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-md-5 col-lg-4 mb-2 mb-md-0">
                <x-adminlte-input name="search" placeholder="Search pages..." wire:model.debounce.500ms="search"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @can('create', \App\Models\Content::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New Page" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#contentFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th style="width:40%;" class="align-middle">Page</th>
                        <th style="width:16%;" class="align-middle">Group</th>
                        <th class="align-middle">Page URL</th>
                        <th style="width:130px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contents as $content)
                        <tr wire:key="content-row-{{ $content->id }}">
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $content->image_url ?: asset('image/placeholder.jpg') }}"
                                        alt="" class="rounded mr-2"
                                        style="width: 36px; height: 36px; object-fit: cover; flex: 0 0 36px;">

                                    <span class="font-weight-bold text-truncate">{{ $content->title }}</span>
                                </div>
                            </td>
                            <td class="text-muted small align-middle">
                                @if ($content->group)
                                    <span class="badge badge-light border">{{ $content->group }}</span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="align-middle text-truncate">
                                <a href="{{ $content->url }}" target="_blank" rel="noopener"
                                    class="small text-primary">/content/{{ $content->slug }}</a>
                            </td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.admin.hoa-center.contents.partials.content-actions', [
                                    'actionKey' => 'Desktop',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-file-alt fa-2x mb-2"></i>
                                <p class="mb-0">
                                    @if ($search !== '')
                                        No pages match your search.
                                    @else
                                        No pages yet. A new page appears under About Us in the public menu.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS                                                        --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($contents as $content)
            <div class="card mb-2 shadow-sm" wire:key="content-card-{{ $content->id }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="font-weight-bold pr-2 text-break">{{ $content->title }}</span>

                        @if ($content->group)
                            <span class="badge badge-light border text-nowrap">{{ $content->group }}</span>
                        @endif
                    </div>

                    <div class="text-muted small mb-3 text-break">
                        <a href="{{ $content->url }}" target="_blank" rel="noopener">/content/{{ $content->slug }}</a>
                    </div>

                    <div class="float-right">
                        @include('livewire.admin.hoa-center.contents.partials.content-actions', [
                            'actionKey' => 'Mobile',
                        ])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-file-alt fa-2x mb-2"></i>
                    <p class="mb-0">
                        @if ($search !== '')
                            No pages match your search.
                        @else
                            No pages yet. A new page appears under About Us in the public menu.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($contents->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $contents->links() }}
        </div>
    @endif

    @include('livewire.admin.settings.partials.summernote')
</div>
