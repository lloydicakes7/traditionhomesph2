<div>
    {{-- =================================================================== --}}
    {{-- PAGE ACTIONS: what a biller starts from, beside the door to the       --}}
    {{-- recurring dues this page deliberately excludes                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card class="mb-3">
        <div class="row justify-content-end">
            <div class="col-12 col-sm-auto mb-1 mb-sm-0">
                @can('viewAnyInvoices', \App\Models\Bill\Bill::class)
                    <a href="{{ route('collections.monthly-dues-invoices') }}"
                        class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-fw fa-calendar-check"></i> View Monthly Dues Invoices
                    </a>
                @endcan
            </div>
            <div class="col-12 col-sm-auto">
                @can('exportInvoices', \App\Models\Bill\Bill::class)
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
                <x-adminlte-input name="search" placeholder="Search Ref. # or Name"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterMonth" wire:model="filterMonth" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Months</option>
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-lg-auto ml-auto">
                @can('createInvoice', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Add Invoice" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#invoiceFormModal" class="btn-sm btn-block" />
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
            @can('deleteInvoice', \App\Models\Bill\Bill::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#invoiceMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('deleteInvoice', \App\Models\Bill\Bill::class)
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
                        <th class="align-middle" style="width: 27%;">Homeowner</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('total')" style="width: 20%;">
                            Amount
                            @if ($sortField === 'total')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('balance')" style="width: 15%;">
                            Paid / Balance
                            @if ($sortField === 'balance')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr wire:key="invoice-row-{{ $invoice->id }}">
                            @can('deleteInvoice', \App\Models\Bill\Bill::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="invoice-{{ $invoice->id }}" value="{{ $invoice->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="invoice-{{ $invoice->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="font-weight-medium text-truncate">{{ $invoice->reference }}</div>
                                <div class="small text-muted">
                                    {{ $invoice->bill_date_display }}
                                    &middot;
                                    <span class="badge badge-{{ $invoice->status_badge }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="text-truncate">{{ $invoice->issued_to_name }}</div>
                            </td>
                            <td class="align-middle text-right">
                                <div class="small">{{ $invoice->total_display }}</div>
                                @if ($invoice->discount > 0 || $invoice->penalty > 0)
                                    <div class="text-muted small">
                                        @if ($invoice->discount > 0)
                                            <span class="text-success">-{{ $invoice->discount_display }} disc.</span>
                                        @endif
                                        @if ($invoice->penalty > 0)
                                            <span class="text-danger">+{{ $invoice->penalty_display }} pen.</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                <div class="small text-muted">Paid: {{ $invoice->paid_display }}</div>
                                <div class="small font-weight-medium">{{ $invoice->balance_display }}</div>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="invoiceActions{{ $invoice->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="invoiceActions{{ $invoice->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#invoiceViewModal" data-id="{{ $invoice->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Details
                                        </a>

                                        @can('create', \App\Models\Payment::class)
                                            @if ($invoice->status_enum && $invoice->status_enum->isCollectible())
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="recordPayment({{ $invoice->id }})">
                                                    <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Record Payment
                                                </a>
                                            @endif
                                        @endcan

                                        @can('updateInvoice', \App\Models\Bill\Bill::class)
                                            @if ($invoice->status_enum && $invoice->status_enum->isCollectible())
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#invoiceFormModal" data-id="{{ $invoice->id }}">
                                                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item text-warning" href="#"
                                                    data-toggle="modal" data-target="#invoiceCancelModal" data-id="{{ $invoice->id }}">
                                                    <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel
                                                </a>
                                            @endif
                                        @endcan

                                        @can('deleteInvoice', \App\Models\Bill\Bill::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#invoiceDeleteModal" data-id="{{ $invoice->id }}">
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
                                <i class="fas fa-fw fa-file-invoice-dollar fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterMonth
                                        ? 'No invoices match your filters.'
                                        : 'No invoices raised yet. Add one to bill a homeowner for something outside the monthly dues.' }}
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
        @forelse ($invoices as $invoice)
            <div class="card mb-3 shadow-sm" wire:key="invoice-card-{{ $invoice->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('deleteInvoice', \App\Models\Bill\Bill::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-invoice-{{ $invoice->id }}" value="{{ $invoice->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-invoice-{{ $invoice->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $invoice->reference }}
                                </div>
                                <div class="small text-muted">{{ $invoice->bill_date_display }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $invoice->status_badge }} flex-shrink-0">
                            {{ $invoice->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $invoice->property?->block_lot_label ?: '—' }}</div>
                    <div class="text-muted small text-truncate mb-2">{{ $invoice->issued_to_name }}</div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                            <div class="small">{{ $invoice->total_display }}</div>
                            @if ($invoice->discount > 0 || $invoice->penalty > 0)
                                <div class="small">
                                    @if ($invoice->discount > 0)
                                        <span class="text-success">-{{ $invoice->discount_display }} disc.</span>
                                    @endif
                                    @if ($invoice->penalty > 0)
                                        <span class="text-danger">+{{ $invoice->penalty_display }} pen.</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">PAID / BALANCE</div>
                            <div class="small text-muted">Paid: {{ $invoice->paid_display }}</div>
                            <div class="small font-weight-medium">{{ $invoice->balance_display }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobInvoiceActions{{ $invoice->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobInvoiceActions{{ $invoice->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#invoiceViewModal" data-id="{{ $invoice->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> View Details
                            </a>

                            @can('create', \App\Models\Payment::class)
                                @if ($invoice->status_enum && $invoice->status_enum->isCollectible())
                                    <a class="dropdown-item" href="#"
                                        wire:click.prevent="recordPayment({{ $invoice->id }})">
                                        <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Record Payment
                                    </a>
                                @endif
                            @endcan

                            @can('updateInvoice', \App\Models\Bill\Bill::class)
                                @if ($invoice->status_enum && $invoice->status_enum->isCollectible())
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#invoiceFormModal" data-id="{{ $invoice->id }}">
                                        <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-warning" href="#"
                                        data-toggle="modal" data-target="#invoiceCancelModal" data-id="{{ $invoice->id }}">
                                        <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel
                                    </a>
                                @endif
                            @endcan

                            @can('deleteInvoice', \App\Models\Bill\Bill::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#invoiceDeleteModal" data-id="{{ $invoice->id }}">
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
                    <i class="fas fa-fw fa-file-invoice-dollar fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterMonth
                            ? 'No invoices match your filters.'
                            : 'No invoices raised yet. Add one to bill a homeowner for something outside the monthly dues.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($invoices->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $invoices->links() }}
        </div>
    @endif

</div>
