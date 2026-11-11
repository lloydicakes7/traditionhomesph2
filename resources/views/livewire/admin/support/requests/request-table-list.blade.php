<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                    --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search homeowner, details..."
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
                <x-adminlte-select name="filterType" wire:model="filterType" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Request Types</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Request::class)
                    <x-adminlte-button label="New Request" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#requestFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Request::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#requestMassDeleteModal"
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
                        @can('delete', \App\Models\Request::class)
                            <th class="text-center align-middle" style="width:5%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('resident')" style="width:22%;">
                            Homeowner
                            @if ($sortField === 'resident')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('type')" style="width:27%;">
                            Request
                            @if ($sortField === 'type')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('status')" style="width:15%;">
                            Status
                            @if ($sortField === 'status')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:16%;">Payment</th>
                        <th class="text-center align-middle" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        {{-- Keyed by the request itself, the way the mobile cards
                             below are: a row is a record, not a position, so a
                             deleted one leaves and the rest keep their own
                             bindings instead of the diff shifting them up. --}}
                        <tr wire:key="request-row-{{ $request->id }}">
                            @can('delete', \App\Models\Request::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="request-{{ $request->id }}" value="{{ $request->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="request-{{ $request->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $request->resident_label }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : '—' }}
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">
                                    @if ($request->type)
                                        <i class="{{ $request->type->icon() }} mr-1 text-muted"></i>
                                    @endif
                                    {{ $request->type_label }}
                                </div>
                                <div class="text-muted small text-truncate">{{ $request->summary }}</div>
                                <div class="text-muted small text-truncate">{{ $request->reference ?: '—' }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $request->status_badge_theme }}">
                                    {{ $request->status_label }}
                                </span>
                                @if ($request->is_processed)
                                    <div class="text-muted small">Processed</div>
                                @endif
                            </td>
                            {{-- Whether the invoice this request carries has been
                                 paid — the fact that releases its document. --}}
                            <td class="align-middle text-truncate" style="max-width:0;">
                                @if (! $request->has_invoice)
                                    <span class="text-muted small">Not billed</span>
                                @else
                                    <span class="badge badge-{{ $request->bill->status_badge }}">
                                        {{ $request->bill->status_label }}
                                    </span>
                                    <div class="text-muted small text-truncate">{{ $request->bill->reference }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @include('livewire.admin.support.requests.partials.request-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="far fa-fw fa-clipboard fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterType
                                        ? 'No requests match your filters.'
                                        : 'No requests on file. Homeowners file them from the portal.' }}
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
    {{-- The same record as the desktop row, laid out as a stacked card, with --}}
    {{-- the same moves in the card footer.                                  --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($requests as $request)
            <div class="card mb-3 shadow-sm" wire:key="request-card-{{ $request->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Request::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-request-{{ $request->id }}" value="{{ $request->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-request-{{ $request->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $request->resident_label }}
                                </div>
                                <div class="small text-muted text-truncate">{{ $request->type_label }}</div>
                                <div class="small text-muted text-truncate">{{ $request->reference ?: '—' }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $request->status_badge_theme }} flex-shrink-0">
                            {{ $request->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @foreach ($request->details() as $label => $value)
                        <div class="d-flex justify-content-between align-items-start mb-1" style="gap:0.75rem;">
                            <div class="text-muted small flex-shrink-0">{{ $label }}</div>
                            <div class="small font-weight-medium text-right text-break">{{ $value }}</div>
                        </div>
                    @endforeach

                    <div class="text-muted small mt-2" style="font-size:0.7rem;">
                        FILED {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : '—' }}
                    </div>
                    <div class="mt-1">
                        @if (! $request->has_invoice)
                            <span class="text-muted small">Not billed</span>
                        @else
                            <span class="badge badge-{{ $request->bill->status_badge }}">
                                <i class="fas fa-fw fa-file-invoice-dollar"></i> {{ $request->bill->status_label }}
                            </span>
                            <span class="text-muted small">{{ $request->bill->reference }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    @include('livewire.admin.support.requests.partials.request-actions', ['actionKey' => 'Mobile'])
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="far fa-fw fa-clipboard fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterType
                            ? 'No requests match your filters.'
                            : 'No requests on file. Homeowners file them from the portal.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($requests->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $requests->links() }}
        </div>
    @endif
</div>
