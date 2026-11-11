<div>
    {{-- =================================================================== --}}
    {{-- PREPARE A PERIOD'S STATEMENTS                                        --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center gap-2">
            <div class="col-12 col-md-5">
                <x-adminlte-select name="preparePeriod" label="Statement Period"
                    wire:model="preparePeriod" igroup-size="sm">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
                <p class="mb-1 text-muted small">
                    > Preparing Statements covers every homeowner account with an email on file that still has outstanding charges.
                </p>
                <p class="mb-0 text-muted small">
                    > Sending and Retrying mails from the selected ones sends a statements of their outstanding period using email.
                </p>
            </div>
            <div class="col-12 col-md-auto ml-auto">
                @can('create', \App\Models\SoaEmail::class)
                    <x-adminlte-button label="Prepare Statements" theme="primary" icon="fas fa-fw fa-file-alt"
                        wire:click="prepare" wire:loading.attr="disabled"
                        data-thha-progress="Preparing statements of account…" class="btn-sm btn-block" />
                @endcan
                @can('send', \App\Models\SoaEmail::class)
                    <x-adminlte-button label="Send / Retry Selected" theme="success" icon="fas fa-fw fa-paper-plane"
                        wire:click="sendSelected" wire:loading.attr="disabled"
                        data-thha-progress="Sending statements of account…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- FILTERS                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search address, property or period"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-lg-2 mb-2 mb-sm-0">
                <x-adminlte-select name="filterPeriod" wire:model="filterPeriod" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Periods</option>
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-lg-2">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
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
            @can('delete', \App\Models\SoaEmail::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#soaMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- The homeowner and its email share one cell, and the period folds into --}}
    {{-- the property cell, so the table fits the viewport on every device.    --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('delete', \App\Models\SoaEmail::class)
                            <th class="text-center align-middle" style="width: 4%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="align-middle" style="width: 22%;">Period</th>
                        <th class="align-middle" style="width: 32%;">Homeowner / Email</th>
                        <th class="align-middle" style="width: 20%;">Sent</th>
                        <th class="align-middle text-center" style="width: 12%;">Status</th>
                        <th class="text-center align-middle" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($statements as $statement)
                        <tr wire:key="soa-row-{{ $statement->id }}">
                            @can('delete', \App\Models\SoaEmail::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="soa-{{ $statement->id }}" value="{{ $statement->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="soa-{{ $statement->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="text-truncate">{{ $statement->period_display }}</div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="small text-truncate">{{ $statement->issued_to_name }}</div>
                                <div class="text-muted small text-truncate">{{ $statement->email }}</div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <span class="small text-truncate d-block">{{ $statement->sent_at_display }}</span>
                                @if ($statement->error)
                                    <span class="text-danger small text-truncate d-block" title="{{ $statement->error }}">
                                        {{ $statement->error }}
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $statement->status_enum?->badgeTheme() ?? 'secondary' }}">
                                    {{ $statement->status_enum?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="soaActions{{ $statement->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="soaActions{{ $statement->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#soaViewModal" data-id="{{ $statement->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> Preview Statement
                                        </a>
                                        @if ($statement->email)
                                            @can('send', \App\Models\SoaEmail::class)
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="sendOne({{ $statement->id }})"
                                                    wire:loading.attr="disabled" wire:target="sendOne({{ $statement->id }})">
                                                    <i class="fas fa-fw fa-paper-plane mr-1"></i> Send / Retry
                                                </a>
                                            @endcan
                                        @else
                                            <a class="dropdown-item disabled" href="#" title="No email address on file">
                                                <i class="fas fa-fw fa-paper-plane mr-1"></i> Send / Retry — no email on file
                                            </a>
                                        @endif
                                        @can('delete', \App\Models\SoaEmail::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#soaDeleteModal" data-id="{{ $statement->id }}">
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
                                <i class="fas fa-fw fa-envelope-open-text fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterPeriod
                                        ? 'No statements match your filters.'
                                        : 'No statements prepared yet. Choose a period above and prepare a batch.' }}
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
        @forelse ($statements as $statement)
            <div class="card mb-3 shadow-sm" wire:key="soa-card-{{ $statement->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\SoaEmail::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-soa-{{ $statement->id }}" value="{{ $statement->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-soa-{{ $statement->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $statement->property_label }}
                                </div>
                                <div class="small text-muted">{{ $statement->period_display }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $statement->status_enum?->badgeTheme() ?? 'secondary' }} flex-shrink-0">
                            {{ $statement->status_enum?->label() ?? '—' }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $statement->issued_to_name }}</div>
                    <div class="text-muted small text-truncate mb-2">{{ $statement->email }}</div>

                    <div class="text-muted small" style="font-size:0.7rem;">SENT</div>
                    <div class="small">{{ $statement->sent_at_display }}</div>
                    @if ($statement->error)
                        <div class="text-danger small text-truncate mt-1" title="{{ $statement->error }}">
                            {{ $statement->error }}
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobSoaActions{{ $statement->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobSoaActions{{ $statement->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#soaViewModal" data-id="{{ $statement->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> Preview Statement
                            </a>
                            @if ($statement->email)
                                @can('send', \App\Models\SoaEmail::class)
                                    <a class="dropdown-item" href="#"
                                        wire:click.prevent="sendOne({{ $statement->id }})"
                                        wire:loading.attr="disabled" wire:target="sendOne({{ $statement->id }})">
                                        <i class="fas fa-fw fa-paper-plane mr-1"></i> Send / Retry
                                    </a>
                                @endcan
                            @else
                                <a class="dropdown-item disabled" href="#" title="No email address on file">
                                    <i class="fas fa-fw fa-paper-plane mr-1"></i> Send / Retry — no email on file
                                </a>
                            @endif
                            @can('delete', \App\Models\SoaEmail::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#soaDeleteModal" data-id="{{ $statement->id }}">
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
                    <i class="fas fa-fw fa-envelope-open-text fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterPeriod
                            ? 'No statements match your filters.'
                            : 'No statements prepared yet. Choose a period above and prepare a batch.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($statements->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $statements->links() }}
        </div>
    @endif

</div>
