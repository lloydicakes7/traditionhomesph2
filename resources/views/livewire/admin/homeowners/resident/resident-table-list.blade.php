<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + ADD BUTTON                                                 --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-centerr">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search by name, email, mobile no..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            @can('create', \App\Models\Resident::class)
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="Add Resident" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#residentFormModal" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MASS DELETE BAR                                                     --}}
    {{-- =================================================================== --}}
    @if (!empty($selectedIds))
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="text-muted small font-weight-bold">
                <i class="fas fa-fw fa-check-square"></i>
                {{ count($selectedIds) }} selected
            </span>
            @can('delete', \App\Models\Resident::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#residentMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Resident::class)
                            <th style="width:30px;" class="text-center align-middle">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('surname')" style="width:21%;">
                            Name
                            @if ($sortField === 'surname')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:11%;">Unit</th>
                        <th class="align-middle" style="width:11%;">Civil Status</th>
                        <th class="align-middle" style="width:13%;">Mobile No.</th>
                        <th class="align-middle" style="width:8%;">Sex</th>
                        <th class="align-middle text-center" style="width:6%;">Age</th>
                        <th class="align-middle" style="width:17%;">Account</th>
                        <th style="width:130px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($residents as $resident)
                        <tr wire:key="resident-row-{{ $resident->id }}">
                            @can('delete', \App\Models\Resident::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="res-{{ $resident->id }}" value="{{ $resident->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="res-{{ $resident->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle">
                                <div class="d-flex align-items-center" style="gap:0.75rem;">
                                    @if ($resident->photo_url)
                                        <img src="{{ $resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;">
                                    @else
                                        <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;">
                                    @endif
                                    <div class="min-width-0">
                                        <span class="font-weight-medium">{{ $resident->full_name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-nowrap">
                                @if ($resident->household?->property)
                                    <small>{{ $resident->household->property->block_lot_label }}</small>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td class="align-middle text-nowrap">
                                <small>{{ $resident->civil_status_label }}</small>
                            </td>
                            <td class="align-middle">
                                @if ($resident->mobile_no)
                                    <small>{{ $resident->mobile_no }}</small>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <small>{{ $resident->sex_label }}</small>
                            </td>
                            <td class="text-center align-middle">
                                <span class="font-weight-medium">{{ $resident->age ?? '—' }}</span>
                            </td>
                            <td class="align-middle">
                                @if ($resident->user)
                                    <span class="badge badge-success" style="font-size:0.65rem;">Linked</span>
                                    <br><small class="text-muted">{{ $resident->user->email }}</small>
                                @else
                                    <span class="badge badge-secondary" style="font-size:0.65rem;">resident</span>
                                @endif
                            </td>
                            <td class="text-right align-middle text-nowrap" style="overflow: visible;">
                                @include('livewire.admin.homeowners.resident.partials.resident-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                                <p class="mb-0">{{ $search ? 'No residents match your search.' : 'No residents added yet.' }}</p>
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
        @forelse ($residents as $resident)
            <div class="card mb-2 shadow-sm" wire:key="resident-card-{{ $resident->id }}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center mb-2" style="gap:0.6rem;">
                        @can('delete', \App\Models\Resident::class)
                            <div class="custom-control custom-checkbox flex-shrink-0">
                                <input type="checkbox" class="custom-control-input"
                                    id="mob-res-{{ $resident->id }}" value="{{ $resident->id }}"
                                    wire:model="selectedIds">
                                <label class="custom-control-label" for="mob-res-{{ $resident->id }}"></label>
                            </div>
                        @endcan
                        @if ($resident->photo_url)
                            <img src="{{ $resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                style="width:40px; height:40px; object-fit:cover;">
                        @else
                            <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                style="width:40px; height:40px; object-fit:cover;">
                        @endif
                        <span class="font-weight-bold" style="font-size:0.95rem;">{{ $resident->full_name }}</span>
                    </div>

                    <div class="d-flex flex-column mt-2" style="gap:0.3rem;">
                        @if ($resident->mobile_no)
                            <span class="small"><i class="fas fa-fw fa-mobile-alt text-muted mr-1" style="width:14px;"></i>{{ $resident->mobile_no }}</span>
                        @endif
                        <span class="small"><i class="fas fa-fw fa-venus-mars text-muted mr-1" style="width:14px;"></i>{{ $resident->sex_label }}</span>
                        <span class="small"><i class="fas fa-fw fa-home text-muted mr-1" style="width:14px;"></i>
                            Unit: {{ $resident->household?->property?->block_lot_label ?? 'None' }}
                        </span>
                        <span class="small"><i class="fas fa-fw fa-ring text-muted mr-1" style="width:14px;"></i>{{ $resident->civil_status_label }}</span>
                        <span class="small"><i class="fas fa-fw fa-calendar text-muted mr-1" style="width:14px;"></i>Age: {{ $resident->age ?? '—' }}</span>
                        <span class="small">
                            <i class="fas fa-fw fa-user-circle text-muted mr-1" style="width:14px;"></i>
                            Account:
                            @if ($resident->user)
                                <span class="badge badge-success" style="font-size:0.6rem;">Linked</span>
                                <small>{{ $resident->user->email }}</small>
                            @else
                                <span class="badge badge-secondary" style="font-size:0.6rem;">None</span>
                            @endif
                        </span>
                    </div>

                    <div class="float-right mt-2">
                        @include('livewire.admin.homeowners.resident.partials.resident-actions', ['actionKey' => 'Mobile'])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No residents match your search.' : 'No residents added yet.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($residents->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $residents->links() }}
        </div>
    @endif
</div>
