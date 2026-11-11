<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + FILE BUTTON                                      --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search subject, submitter, details..."
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

            <div class="col-6 col-sm-3 col-lg-3 mb-2 mb-md-0">
                <x-adminlte-select name="filterCategory" wire:model="filterCategory" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Categories</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Complaint::class)
                    <x-adminlte-button label="File a Complaint" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#complaintFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Complaint::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#complaintMassDeleteModal"
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
                        @can('delete', \App\Models\Complaint::class)
                            <th class="text-center align-middle" style="width:5%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('subject')" style="width:30%;">
                            Complaint
                            @if ($sortField === 'subject')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('category')" style="width:15%;">
                            Category
                            @if ($sortField === 'category')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:20%;">Submitted By</th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('status')" style="width:15%;">
                            Status
                            @if ($sortField === 'status')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($complaints as $complaint)
                        <tr wire:key="complaint-row-{{ $complaint->id }}">
                            @can('delete', \App\Models\Complaint::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="complaint-{{ $complaint->id }}" value="{{ $complaint->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="complaint-{{ $complaint->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $complaint->subject }}</div>
                                <div class="text-muted small text-truncate">{{ \Illuminate\Support\Str::limit($complaint->description, 90) }}</div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="small text-truncate">{{ $complaint->category_label }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $complaint->submitted_by_homeowner ? 'From the portal' : 'Filed by the office' }}
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="text-truncate">{{ $complaint->submitter_label }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : '—' }}
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $complaint->status_badge_theme }}">
                                    {{ $complaint->status_label }}
                                </span>
                                <div class="text-muted small">
                                    {{ $complaint->is_responded ? 'Answered' : 'No response yet' }}
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                @include('livewire.admin.support.complaints.partials.complaint-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-question-circle fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterCategory
                                        ? 'No complaints match your filters.'
                                        : 'No complaints on file. Homeowners file them from the portal, or the office records one here.' }}
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
        @forelse ($complaints as $complaint)
            <div class="card mb-3 shadow-sm" wire:key="complaint-card-{{ $complaint->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Complaint::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-complaint-{{ $complaint->id }}" value="{{ $complaint->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-complaint-{{ $complaint->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $complaint->subject }}
                                </div>
                                <div class="small text-muted text-truncate">{{ $complaint->category_label }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $complaint->status_badge_theme }} flex-shrink-0">
                            {{ $complaint->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($complaint->description, 140) }}</p>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">SUBMITTED BY</div>
                            <div class="small font-weight-medium">{{ $complaint->submitter_label }}</div>
                            <div class="text-muted small">
                                {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : '—' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">RESPONSE</div>
                            <div class="small font-weight-medium">
                                {{ $complaint->is_responded ? 'Answered' : 'Not yet answered' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    @include('livewire.admin.support.complaints.partials.complaint-actions', ['actionKey' => 'Mobile'])
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-question-circle fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterCategory
                            ? 'No complaints match your filters.'
                            : 'No complaints on file. Homeowners file them from the portal, or the office records one here.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($complaints->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $complaints->links() }}
        </div>
    @endif
</div>
