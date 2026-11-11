<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + ADD BUTTON                                                 --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search by member name or property..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Household\Household::class)
                    <x-adminlte-button label="Add Household" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#householdFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
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
                        <th class="align-middle" style="width:25%;">Property</th>
                        <th class="align-middle" style="width:27%;">Members</th>
                        <th class="align-middle text-center" style="width:8%;">Age</th>
                        <th class="align-middle" style="width:14%;">Relations</th>
                        <th class="align-middle" style="width:12%;">Created</th>
                        <th style="width:14%;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($households as $household)
                        @php($members = $household->ordered_members)
                        @php($span = max(1, $members->count()))

                        @foreach ($members as $index => $mr)
                            <tr wire:key="household-row-{{ $household->id }}-{{ $mr->id }}">
                                @if ($index === 0)
                                    {{-- Property --}}
                                    <td rowspan="{{ $span }}" class="align-middle">
                                        <div class="font-weight-medium">
                                            @if ($household->property)
                                                {{ $household->property->block_lot_label }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                        @if ($household->property?->address)
                                            <small class="text-muted d-block">{{ $household->property->address->subdivision }}</small>
                                        @endif
                                        <div class="mt-1">
                                            <span class="badge badge-{{ $household->type_enum?->badgeTheme() ?? 'secondary' }}" style="font-size:0.65rem;">
                                                {{ $household->type_enum?->label() ?? $household->type }}
                                            </span>
                                        </div>
                                    </td>
                                @endif

                                {{-- Member — head first, crown on the head --}}
                                <td class="align-middle">
                                    <div class="d-flex align-items-center" style="gap:0.5rem;">
                                        @if ($mr->resident?->photo_url)
                                            <img src="{{ $mr->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                                style="width:28px; height:28px; object-fit:cover;" loading="lazy">
                                        @else
                                            <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                                style="width:28px; height:28px; object-fit:cover;" loading="lazy">
                                        @endif
                                        <span class="{{ $mr->is_head ? 'font-weight-medium' : '' }} small">
                                            @if ($mr->is_head)
                                                <i class="fas fa-fw fa-crown text-warning mr-1" title="Head of the family"></i>
                                            @endif
                                            {{ $mr->resident?->full_name ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="align-middle text-center">
                                    <small class="{{ $mr->is_head ? 'font-weight-medium' : '' }}">{{ $mr->resident?->age ?? '0' }}</small>
                                </td>

                                <td class="align-middle">
                                    <small class="{{ $mr->is_head ? 'font-weight-medium' : '' }}">{{ $mr->relationship_label }}</small>
                                </td>

                                @if ($index === 0)
                                    {{-- Created --}}
                                    <td rowspan="{{ $span }}" class="align-middle">
                                        <small class="text-muted">{{ $household->created_at?->format('M d, Y') ?? '—' }}</small>
                                    </td>

                                    {{-- Actions --}}
                                    <td rowspan="{{ $span }}" class="text-right align-middle text-nowrap" style="overflow: visible;">
                                        @include('livewire.admin.homeowners.household.partials.household-actions', ['actionKey' => 'Desktop'])
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        @if ($members->isEmpty())
                            <tr wire:key="household-row-{{ $household->id }}">
                                <td class="align-middle">
                                    <div class="font-weight-medium">
                                        @if ($household->property)
                                            {{ $household->property->block_lot_label }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                    @if ($household->property?->address)
                                        <small class="text-muted d-block">{{ $household->property->address->subdivision }}</small>
                                    @endif
                                    <div class="mt-1">
                                        <span class="badge badge-{{ $household->type_enum?->badgeTheme() ?? 'secondary' }}" style="font-size:0.65rem;">
                                            {{ $household->type_enum?->label() ?? $household->type }}
                                        </span>
                                    </div>
                                </td>
                                <td colspan="3" class="align-middle text-muted small">No members on record.</td>
                                <td class="align-middle">
                                    <small class="text-muted">{{ $household->created_at?->format('M d, Y') ?? '—' }}</small>
                                </td>
                                <td class="text-right align-middle text-nowrap" style="overflow: visible;">
                                    @include('livewire.admin.homeowners.household.partials.household-actions', ['actionKey' => 'Desktop'])
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                                <p class="mb-0">{{ $search ? 'No households match your search.' : 'No households added yet.' }}</p>
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
        @forelse ($households as $household)
            <div class="card mb-2 shadow-sm" wire:key="household-card-{{ $household->id }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="font-weight-bold">
                            {{ $household->head_resident?->full_name ?? 'No head on record' }}
                        </span>
                        <span class="badge badge-{{ $household->type_enum?->badgeTheme() ?? 'secondary' }}" style="font-size:0.6rem;">
                            {{ $household->type_enum?->label() ?? $household->type }}
                        </span>
                    </div>

                    <div class="small mb-1">
                        <strong>Property:</strong> {{ $household->property_display }}
                    </div>

                    <div class="small mb-1"><strong>Members:</strong></div>
                    <div class="mb-2 pl-2" style="border-left:2px solid #dee2e6;">
                        @foreach ($household->ordered_members as $mr)
                            <div class="d-flex align-items-center mb-1" style="gap:0.5rem;">
                                @if ($mr->resident?->photo_url)
                                    <img src="{{ $mr->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                        style="width:26px; height:26px; object-fit:cover;" loading="lazy">
                                @else
                                    <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                        style="width:26px; height:26px; object-fit:cover;" loading="lazy">
                                @endif
                                <small class="{{ $mr->is_head ? 'font-weight-medium' : '' }}">
                                    @if ($mr->is_head)
                                        <i class="fas fa-fw fa-crown text-warning"></i>
                                    @endif
                                    {{ $mr->resident?->full_name ?? '—' }}
                                </small>
                                <small class="text-muted">({{ $mr->relationship_label }})</small>
                            </div>
                        @endforeach
                    </div>

                    <div class="small text-muted mb-2">
                        Created: {{ $household->created_at?->format('M d, Y') ?? '—' }}
                    </div>

                    <div class="float-right">
                        @include('livewire.admin.homeowners.household.partials.household-actions', ['actionKey' => 'Mobile'])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No households match your search.' : 'No households added yet.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($households->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $households->links() }}
        </div>
    @endif
</div>
