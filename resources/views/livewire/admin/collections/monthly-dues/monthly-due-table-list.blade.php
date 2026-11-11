<div>
    {{-- =================================================================== --}}
    {{-- PAGE ACTIONS: the doors out of this list, inside their own card      --}}
    {{-- =================================================================== --}}
    <x-adminlte-card class="mb-3">
        <div class="row justify-content-end">
            <div class="col-12 col-sm-auto mb-1 mb-sm-0">
                @can('viewAnyMonthlyDues', \App\Models\Bill\Bill::class)
                    <a href="{{ route('collections.monthly-dues-generator') }}"
                        class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-fw fa-cogs"></i> Monthly Dues Generator
                    </a>
                @endcan
            </div>
            <div class="col-12 col-sm-auto">
                @can('exportMonthlyDues', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-export"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search Ref. #, property or homeowner"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-3 col-lg-2 mb-2 mb-sm-0">
                <x-adminlte-select name="filterMonth" wire:model="filterMonth" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Months</option>
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-3 col-lg-2 mb-2 mb-sm-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-4 col-lg-auto ml-auto">
                @can('generateMonthlyDues', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Add Monthly Dues" theme="primary" icon="fas fa-fw fa-calendar-plus"
                        data-toggle="modal" data-target="#pastMonthlyDueModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MASS DELETE BAR                                                      --}}
    {{-- =================================================================== --}}
    @if (!empty($selectedIds))
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="text-muted small font-weight-bold">
                <i class="fas fa-fw fa-check-square"></i>
                {{ count($selectedIds) }} selected
            </span>
            @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#dueMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- Column strategy: table-layout:fixed with widths that sum to 100%,    --}}
    {{-- so the table always fits the viewport instead of overflowing it.     --}}
    {{-- Penalty/Discount are folded into the "Amount" cell instead of        --}}
    {{-- getting their own columns; row actions collapse into one dropdown.   --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                            <th class="text-center align-middle" style="width: 3%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('reference')" style="width: 18%;">
                            Reference #
                            @if ($sortField === 'reference')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width: 27%;">Property</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('total')" style="width: 20%;">
                            Amount
                            @if ($sortField === 'total')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('balance')" style="width: 18%;">
                            Paid / Balance
                            @if ($sortField === 'balance')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width: 7%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dues as $due)
                        <tr wire:key="monthly-due-row-{{ $due->id }}">
                            @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="due-{{ $due->id }}" value="{{ $due->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="due-{{ $due->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="font-weight-medium text-truncate">{{ $due->reference }}</div>
                                <div class="small text-muted">
                                    {{ $due->billing_period_display }}
                                    &middot;
                                    <span class="badge badge-{{ $due->status_badge }}">
                                        {{ $due->status_label }}
                                    </span>
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="small text-truncate">{{ $due->property?->block_lot_label ?: '—' }}</div>
                                <div class="text-muted small text-truncate">{{ $due->issued_to_name }}</div>
                            </td>
                            <td class="align-middle text-right">
                                <div class="small">{{ $due->total_display }}</div>
                                @if ($due->discount > 0 || $due->penalty > 0)
                                    <div class="text-muted small">
                                        @if ($due->discount > 0)
                                            <span class="text-success">-{{ $due->discount_display }} disc.</span>
                                        @endif
                                        @if ($due->penalty > 0)
                                            <span class="text-danger">+{{ $due->penalty_display }} pen.</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                <div class="small text-muted">Paid: {{ $due->paid_display }}</div>
                                <div class="small font-weight-medium">{{ $due->balance_display }}</div>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="dueActions{{ $due->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="dueActions{{ $due->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#dueViewModal" data-id="{{ $due->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Details
                                        </a>

                                        @can('create', \App\Models\Payment::class)
                                            @if ($due->status_enum && $due->status_enum->isCollectible())
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="recordPayment({{ $due->id }})">
                                                    <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Record Payment
                                                </a>
                                            @endif
                                        @endcan

                                        @can('updateMonthlyDue', \App\Models\Bill\Bill::class)
                                            @if ($due->status_enum && $due->status_enum->isCollectible())
                                                <a class="dropdown-item text-warning" href="#"
                                                    data-toggle="modal" data-target="#dueCancelModal" data-id="{{ $due->id }}">
                                                    <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel
                                                </a>
                                            @endif
                                        @endcan

                                        @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#dueDeleteModal" data-id="{{ $due->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-calendar-alt fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterMonth
                                        ? 'No monthly dues match your filters.'
                                        : 'Nothing billed yet. Run the Monthly Dues Generator to raise a month.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS                                                         --}}
    {{-- Same data as the desktop row, laid out as a stacked card so nothing   --}}
    {{-- gets truncated or squeezed on a phone-width screen.                  --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($dues as $due)
            <div class="card mb-3 shadow-sm" wire:key="monthly-due-card-{{ $due->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-due-{{ $due->id }}" value="{{ $due->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-due-{{ $due->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $due->reference }}
                                </div>
                                <div class="small text-muted">{{ $due->billing_period_display }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $due->status_badge }} flex-shrink-0">
                            {{ $due->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $due->property?->block_lot_label ?: '—' }}</div>
                    <div class="text-muted small text-truncate mb-2">{{ $due->issued_to_name }}</div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                            <div class="small">{{ $due->total_display }}</div>
                            @if ($due->discount > 0 || $due->penalty > 0)
                                <div class="small">
                                    @if ($due->discount > 0)
                                        <span class="text-success">-{{ $due->discount_display }} disc.</span>
                                    @endif
                                    @if ($due->penalty > 0)
                                        <span class="text-danger">+{{ $due->penalty_display }} pen.</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">PAID / BALANCE</div>
                            <div class="small text-muted">Paid: {{ $due->paid_display }}</div>
                            <div class="small font-weight-medium">{{ $due->balance_display }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobDueActions{{ $due->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobDueActions{{ $due->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#dueViewModal" data-id="{{ $due->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> View Details
                            </a>

                            @can('create', \App\Models\Payment::class)
                                @if ($due->status_enum && $due->status_enum->isCollectible())
                                    <a class="dropdown-item" href="#"
                                        wire:click.prevent="recordPayment({{ $due->id }})">
                                        <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Record Payment
                                    </a>
                                @endif
                            @endcan

                            @can('updateMonthlyDue', \App\Models\Bill\Bill::class)
                                @if ($due->status_enum && $due->status_enum->isCollectible())
                                    <a class="dropdown-item text-warning" href="#"
                                        data-toggle="modal" data-target="#dueCancelModal" data-id="{{ $due->id }}">
                                        <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel
                                    </a>
                                @endif
                            @endcan

                            @can('deleteMonthlyDue', \App\Models\Bill\Bill::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#dueDeleteModal" data-id="{{ $due->id }}">
                                    <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-calendar-alt fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterMonth
                            ? 'No monthly dues match your filters.'
                            : 'Nothing billed yet. Run the Monthly Dues Generator to raise a month.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($dues->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $dues->links() }}
        </div>
    @endif

</div>
