<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + ADD BUTTON                                       --}}
    {{-- =================================================================== --}}
    <div class="btn btn-success mb-2">
        <p class="mb-0">Credit still held by homeowners: {{ $totalCredit }}</p>

    </div>

    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <x-adminlte-input name="search" placeholder="Search homeowner or property"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-md-2 mb-2 mb-sm-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\AdvanceDeposit::class)
                    <x-adminlte-button label="Add Advance Deposit" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#advanceDepositFormModal" class="btn-sm btn-block d-lg-inline-block" />
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
            @can('delete', \App\Models\AdvanceDeposit::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#advanceDepositMassDeleteModal"
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
                        @can('delete', \App\Models\AdvanceDeposit::class)
                            <th class="text-center align-middle" style="width:4%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="align-middle" style="width:20%;">Homeowner</th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('amount')" style="width:24%;">
                            Received / Deposited
                            @if ($sortField === 'amount')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('balance')" style="width:18%;">
                            Credit Balance
                            @if ($sortField === 'balance')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle text-center" style="width:10%;">Status</th>
                        <th class="text-center align-middle" style="width:12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deposits as $deposit)
                        <tr wire:key="advance-deposit-row-{{ $deposit->id }}">
                            @can('delete', \App\Models\AdvanceDeposit::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="deposit-{{ $deposit->id }}" value="{{ $deposit->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="deposit-{{ $deposit->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="small text-truncate">{{ $deposit->holder_name }}</div>
                            </td>
                            <td class="align-middle">
                                <div class="small">{{ $deposit->amount_display }}</div>
                                <div class="text-muted small">{{ $deposit->deposited_at_display }}</div>
                            </td>
                            <td class="align-middle text-right">
                                <span class="font-weight-medium">{{ $deposit->balance_display }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $deposit->status_enum?->badgeTheme() ?? 'secondary' }}">
                                    {{ $deposit->status_enum?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="depositActions{{ $deposit->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="depositActions{{ $deposit->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#advanceDepositViewModal" data-id="{{ $deposit->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Details
                                        </a>

                                        @can('apply', \App\Models\AdvanceDeposit::class)
                                            @if ($deposit->is_open)
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#advanceDepositApplyModal" data-id="{{ $deposit->id }}">
                                                    <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Apply to a Bill
                                                </a>
                                            @endif
                                        @endcan

                                        @can('refund', \App\Models\AdvanceDeposit::class)
                                            @if ($deposit->is_open)
                                                <a class="dropdown-item text-warning" href="#"
                                                    data-toggle="modal" data-target="#advanceDepositRefundModal" data-id="{{ $deposit->id }}">
                                                    <i class="fas fa-fw fa-undo mr-1"></i> Refund
                                                </a>
                                            @endif
                                        @endcan

                                        @can('delete', \App\Models\AdvanceDeposit::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#advanceDepositDeleteModal" data-id="{{ $deposit->id }}">
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
                                <i class="fas fa-fw fa-piggy-bank fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus
                                        ? 'No advance deposits match your filters.'
                                        : 'No credit held yet. Record a deposit when a homeowner pays ahead of a bill.' }}
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
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($deposits as $deposit)
            <div class="card mb-3 shadow-sm" wire:key="advance-deposit-card-{{ $deposit->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div class="d-flex align-items-start flex-grow-1 mr-2">
                            @can('delete', \App\Models\AdvanceDeposit::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-deposit-{{ $deposit->id }}" value="{{ $deposit->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-deposit-{{ $deposit->id }}"></label>
                                </div>
                            @endcan
                            <div>
                                <div class="font-weight-bold text-break" style="font-size:0.95rem;">
                                    {{ $deposit->holder_name }}
                                </div>
                                <div class="small text-muted text-break">{{ $deposit->property_label }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $deposit->status_enum?->badgeTheme() ?? 'secondary' }}">
                            {{ $deposit->status_enum?->label() ?? '—' }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mt-2" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">RECEIVED / DEPOSITED</div>
                            <div class="small">{{ $deposit->amount_display }}</div>
                            <div class="text-muted small">{{ $deposit->deposited_at_display }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">CREDIT BALANCE</div>
                            <div class="small font-weight-medium">{{ $deposit->balance_display }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobDepositActions{{ $deposit->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobDepositActions{{ $deposit->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#advanceDepositViewModal" data-id="{{ $deposit->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> View Details
                            </a>

                            @can('apply', \App\Models\AdvanceDeposit::class)
                                @if ($deposit->is_open)
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#advanceDepositApplyModal" data-id="{{ $deposit->id }}">
                                        <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Apply to a Bill
                                    </a>
                                @endif
                            @endcan

                            @can('refund', \App\Models\AdvanceDeposit::class)
                                @if ($deposit->is_open)
                                    <a class="dropdown-item text-warning" href="#"
                                        data-toggle="modal" data-target="#advanceDepositRefundModal" data-id="{{ $deposit->id }}">
                                        <i class="fas fa-fw fa-undo mr-1"></i> Refund
                                    </a>
                                @endif
                            @endcan

                            @can('delete', \App\Models\AdvanceDeposit::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#advanceDepositDeleteModal" data-id="{{ $deposit->id }}">
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
                    <i class="fas fa-fw fa-piggy-bank fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus
                            ? 'No advance deposits match your filters.'
                            : 'No credit held yet. Record a deposit when a homeowner pays ahead of a bill.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($deposits->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $deposits->links() }}
        </div>
    @endif

</div>
