<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + ADD BUTTON                                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search properties..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2 mb-md-0">
                <x-adminlte-select name="filterType" wire:model="filterType" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-building"></i></div>
                    </x-slot>
                    <option value="">All Types</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2 mb-md-0">
                <x-adminlte-select name="filterOccupancy" wire:model="filterOccupancy" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-users"></i></div>
                    </x-slot>
                    <option value="">All Occupancy</option>
                    <option value="has_owner">Has Homeowner</option>
                    <option value="vacant">Vacant</option>
                </x-adminlte-select>
            </div>
            <div class="col-12 col-md-auto ml-auto">
                @can('create', \App\Models\Property::class)
                    <x-adminlte-button label="Add Property" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#propertyFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
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
            @can('delete', \App\Models\Property::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#propertyMassDeleteModal"
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
                        @can('delete', \App\Models\Property::class)
                            <th style="width:30px;" class="text-center align-middle">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('block')" style="width:20%;">
                            Block/Lot
                            @if ($sortField === 'block' || $sortField === 'lot')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:20%;">Address</th>
                        <th class="align-middle" style="width:25%;">Homeowner</th>
                        <th class="align-middle" style="width:20%;">SQM / Type</th>
                        <th style="width:130px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        <tr wire:key="property-row-{{ $property->id }}">
                            @can('delete', \App\Models\Property::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="prop-{{ $property->id }}" value="{{ $property->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="prop-{{ $property->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle">
                                <div class="d-flex align-items-center" style="gap:0.6rem;">
                                    <img src="{{ $property->photo_url ?: asset('image/house.png') }}" class="rounded flex-shrink-0"
                                        style="width:44px; height:33px; object-fit:cover;">
                                    <span class="font-weight-medium">{{ $property->block_lot_label }}</span>
                                </div>
                            </td>
                            <td class="align-middle">
                                @if ($property->address)
                                    <small class="text-muted">
                                        {{ $property->address->label }},
                                        {{ $property->address->subdivision }}
                                    </small>
                                @else
                                    <span class="text-danger">—</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if ($property->owner)
                                    <div>
                                        <span class="d-inline-flex align-items-center">
                                            {{ $property->owner->name }}
                                            @can('update', \App\Models\Property::class)
                                                <button type="button" class="btn p-0 ml-2 text-danger"
                                                    wire:click="removeHomeowner({{ $property->id }})"
                                                    wire:loading.attr="disabled"
                                                    title="Remove Homeowner">
                                                    <i class="fas fa-fw fa-times-circle"></i>
                                                </button>
                                            @endcan
                                        </span>
                                        <div class="mt-1" style="line-height:1.3;">
                                            @if ($property->owner->resident && $property->owner->resident->mobile_no)
                                                <small class="text-muted d-block"><i class="fas fa-fw fa-phone mr-1"></i>{{ $property->owner->resident->mobile_no }}</small>
                                            @endif
                                            @if ($property->owner->email)
                                                <small class="text-muted d-block"><i class="fas fa-fw fa-envelope mr-1"></i>{{ $property->owner->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    @can('update', \App\Models\Property::class)
                                        <x-adminlte-button label="" theme="success" icon="fas fa-fw fa-user-plus"
                                            data-toggle="modal" data-target="#propertyAssignHomeownerModal"
                                            data-id="{{ $property->id }}"
                                            class="btn-xs" title="Assign Homeowner" />
                                    @endcan
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="small font-weight-medium">{{ $property->sqm_type_display }}</div>
                                <span class="badge badge-{{ $property->type_enum?->badgeTheme() ?? 'secondary' }} mt-1">
                                    {{ $property->type_enum?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.admin.homeowners.property.partials.property-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-home fa-2x mb-2"></i>
                                <p class="mb-0">{{ $search ? 'No properties match your search.' : 'No properties added yet.' }}</p>
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
        @forelse ($properties as $property)
            <div class="card mb-2 shadow-sm" wire:key="property-card-{{ $property->id }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center" style="gap:0.6rem;">
                            @can('delete', \App\Models\Property::class)
                                <div class="custom-control custom-checkbox flex-shrink-0">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-prop-{{ $property->id }}" value="{{ $property->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-prop-{{ $property->id }}"></label>
                                </div>
                            @endcan
                            <img src="{{ $property->photo_url ?: asset('image/house.png') }}" class="rounded flex-shrink-0"
                                style="width:44px; height:33px; object-fit:cover;">
                            <span class="font-weight-bold">
                                {{ $property->block_lot_label }}
                            </span>
                        </div>
                        <span class="badge badge-{{ $property->type_enum?->badgeTheme() ?? 'secondary' }} flex-shrink-0">
                            {{ $property->type_enum?->label() ?? '' }}
                        </span>
                    </div>

                    {{-- Address line --}}
                    <div class="text-muted small mb-2">
                        @if ($property->address)
                            {{ $property->address->label }}, {{ $property->address->subdivision }}
                        @else
                            <span class="text-danger">No address set</span>
                        @endif
                    </div>

                    {{-- Homeowner + SQM row --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @if ($property->owner)
                                <div class="d-flex align-items-center" style="gap:0.35rem;">
                                    <span class="small font-weight-medium">{{ $property->owner->name }}</span>
                                    @can('update', \App\Models\Property::class)
                                        <button type="button" class="btn p-0 text-danger"
                                            wire:click="removeHomeowner({{ $property->id }})"
                                            wire:loading.attr="disabled"
                                            title="Remove Homeowner">
                                            <i class="fas fa-fw fa-times-circle" style="font-size:0.75rem;"></i>
                                        </button>
                                    @endcan
                                </div>
                                @if ($property->owner->resident && $property->owner->resident->mobile_no)
                                    <small class="text-muted d-block">
                                        <i class="fas fa-fw fa-phone mr-1"></i>{{ $property->owner->resident->mobile_no }}
                                    </small>
                                @endif
                            @else
                                @can('update', \App\Models\Property::class)
                                    <x-adminlte-button label="Assign" theme="success" icon="fas fa-fw fa-user-plus"
                                        data-toggle="modal" data-target="#propertyAssignHomeownerModal"
                                        data-id="{{ $property->id }}"
                                        class="btn-xs" />
                                @endcan
                            @endif
                        </div>
                        <span class="text-muted small">{{ $property->sqm_type_display }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="float-right">
                        @include('livewire.admin.homeowners.property.partials.property-actions', ['actionKey' => 'Mobile'])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-home fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No properties match your search.' : 'No properties added yet.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($properties->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $properties->links() }}
        </div>
    @endif
</div>
