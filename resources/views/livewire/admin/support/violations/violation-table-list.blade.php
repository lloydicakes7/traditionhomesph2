<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + ISSUE BUTTON                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search member, type, location..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-6 col-sm-3 col-lg-2 mb-2 mb-md-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-6 col-sm-4 col-lg-3 mb-2 mb-md-0">
                <x-adminlte-select name="filterType" wire:model="filterType" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Violation Types</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Violation::class)
                    <x-adminlte-button label="Issue Violation" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#violationFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Violation::class)
                <x-adminlte-button label="Mass Delete" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#violationMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Violation::class)
                            <th class="text-center align-middle" style="width:4%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('member')" style="width:20%;">
                            Resident
                            @if ($sortField === 'member')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('violation_type')" style="width:18%;">
                            Violation
                            @if ($sortField === 'violation_type')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('occurred_on')" style="width:24%;">
                            When &amp; Where
                            @if ($sortField === 'occurred_on')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('status')" style="width:18%;">
                            Status
                            @if ($sortField === 'status')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:16%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($violations as $violation)
                        <tr wire:key="violation-row-{{ $violation->id }}">
                            @can('delete', \App\Models\Violation::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="violation-{{ $violation->id }}" value="{{ $violation->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="violation-{{ $violation->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $violation->resident_label }}</div>
                                <div class="text-muted small text-truncate">
                                    @if ($violation->is_acknowledged)
                                        <i class="fas fa-fw fa-check-circle text-success mr-1"></i>Acknowledged
                                        {{ $violation->acknowledged_at->format('M j, Y') }}
                                    @else
                                        <i class="far fa-fw fa-circle mr-1"></i>Awaiting acknowledgment
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $violation->type_label }}</div>
                                @if ($violation->has_office_report)
                                    <div class="text-muted small text-truncate">
                                        <i class="fas fa-fw fa-file-alt mr-1"></i>Office report on file
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="text-truncate">{{ $violation->occurred_display }}</div>
                                <div class="text-muted small text-truncate">
                                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>{{ $violation->location }}
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $violation->status_badge_theme }}">
                                    {{ $violation->status_label }}
                                </span>
                                @if ($violation->resolved_at)
                                    <div class="text-muted small">{{ $violation->resolved_at->format('M j, Y') }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @include('livewire.admin.support.violations.partials.violation-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterType
                                        ? 'No violations match your filters.'
                                        : 'No violations on file. Use “Issue Violation” to cite a homeowner.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE / TABLET CARDS                                               --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($violations as $violation)
            <div class="card mb-3 shadow-sm" wire:key="violation-card-{{ $violation->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Violation::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-violation-{{ $violation->id }}" value="{{ $violation->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-violation-{{ $violation->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $violation->resident_label }}
                                </div>
                                <div class="small text-muted text-truncate">{{ $violation->type_label }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $violation->status_badge_theme }} flex-shrink-0">
                            {{ $violation->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="mb-2">
                        <div class="small text-truncate">
                            <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $violation->occurred_display }}
                        </div>
                        <div class="small text-truncate">
                            <i class="fas fa-fw fa-map-marker-alt mr-1"></i>{{ $violation->location }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">ACKNOWLEDGMENT</div>
                            <div class="small font-weight-medium">
                                {{ $violation->is_acknowledged ? 'Acknowledged' : 'Not yet acknowledged' }}
                            </div>
                            <div class="text-muted small">{{ $violation->acknowledged_display }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">ISSUED BY</div>
                            <div class="small font-weight-medium">{{ $violation->issuedByAdmin->name ?? '—' }}</div>
                            <div class="text-muted small">
                                {{ $violation->issued_at ? $violation->issued_at->format('M j, Y') : 'Not issued' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    @include('livewire.admin.support.violations.partials.violation-actions', ['actionKey' => 'Mobile'])
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterType
                            ? 'No violations match your filters.'
                            : 'No violations on file. Use “Issue Violation” to cite a homeowner.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($violations->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $violations->links() }}
        </div>
    @endif
</div>
