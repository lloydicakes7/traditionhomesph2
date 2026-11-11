<div>
    {{-- A payment was just recorded: print or download its receipt now. --}}
    @if ($recentPayment)
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" wire:click="dismissReceipt" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="font-weight-bold mb-1">
                <i class="fas fa-fw fa-check-circle mr-1"></i>
                {{ $recentPayment->receipt_no }} recorded for {{ $recentPayment->amount_display }}.
            </div>
            <div class="d-flex flex-wrap" style="gap:0.35rem;">
                <a class="btn btn-xs btn-outline-primary"
                    href="{{ route('collections.payments.receipt.show', $recentPayment) }}"
                    data-receipt-print="{{ route('collections.payments.receipt.show', $recentPayment) }}">
                    <i class="fas fa-fw fa-print mr-1"></i> {{ __('payments.receipt.print_a4') }}
                </a>
                <a class="btn btn-xs btn-outline-primary"
                    href="{{ route('collections.payments.receipt.thermal.show', $recentPayment) }}"
                    data-receipt-print="{{ route('collections.payments.receipt.thermal.show', $recentPayment) }}">
                    <i class="fas fa-fw fa-receipt mr-1"></i> {{ __('payments.receipt.print_thermal') }}
                </a>
                <a class="btn btn-xs btn-outline-secondary"
                    href="{{ route('collections.payments.receipt.download', $recentPayment) }}">
                    <i class="fas fa-fw fa-file-pdf mr-1"></i> {{ __('payments.receipt.download_a4') }}
                </a>
                <a class="btn btn-xs btn-outline-secondary"
                    href="{{ route('collections.payments.receipt.thermal.download', $recentPayment) }}">
                    <i class="fas fa-fw fa-file-alt mr-1"></i> {{ __('payments.receipt.download_thermal') }}
                </a>
            </div>
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- PAGE ACTIONS: what a cashier starts from, inside its own card       --}}
    {{-- =================================================================== --}}
    @if (auth()->user()->can('viewAny', \App\Models\Setting::class) || auth()->user()->can('export', \App\Models\Payment::class))
        <x-adminlte-card class="mb-3">
            <div class="row justify-content-end">
                <div class="col-12 col-sm-auto mb-1 mb-sm-0">
                    @can('viewAny', \App\Models\Setting::class)
                        <a href="{{ route('settings.collections') }}" class="btn btn-secondary btn-sm btn-block mb-1 mr-1">
                            <i class="fas fa-fw fa-cog"></i> Settings
                        </a>
                    @endcan
                </div>
                <div class="col-12 col-sm-auto">
                    @can('export', \App\Models\Payment::class)
                        <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-export"
                            wire:click="export" wire:loading.attr="disabled"
                            data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                    @endcan
                </div>
            </div>
        </x-adminlte-card>
    @endif

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search OR number, bill or property"
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
                <x-adminlte-select name="filterMethod" wire:model="filterMethod" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Methods</option>
                    @foreach ($methods as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-lg-auto ml-auto">
                @can('create', \App\Models\Payment::class)
                    <x-adminlte-button label="Record Payment" theme="primary" icon="fas fa-fw fa-hand-holding-usd"
                        data-toggle="modal" data-target="#recordPaymentModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Payment::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#paymentMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- Column strategy: table-layout:fixed with widths that sum to 100%, so  --}}
    {{-- the table fits the viewport; the reference folds into the date cell   --}}
    {{-- and row actions collapse into one dropdown.                          --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th class="align-middle" style="width:23%;">
                            <div class="d-flex align-items-center">
                                @can('delete', \App\Models\Payment::class)
                                    <div class="custom-control custom-checkbox mr-2">
                                        <input type="checkbox" class="custom-control-input" id="selectAll"
                                            wire:model="selectAll">
                                        <label class="custom-control-label" for="selectAll"></label>
                                    </div>
                                @endcan
                                <span class="cursor-pointer" wire:click="sortBy('paid_at')">
                                    Date &amp; OR No.
                                    @if ($sortField === 'paid_at')
                                        <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="align-middle" style="width:20%;">Paid By</th>
                        <th class="align-middle" style="width:20%;">Bill</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('amount')" style="width:20%;">
                            Amount / Method
                            @if ($sortField === 'amount')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:17%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-row-{{ $payment->id }}">
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="d-flex align-items-start">
                                    @can('delete', \App\Models\Payment::class)
                                        <div class="custom-control custom-checkbox mr-2 mt-1">
                                            <input type="checkbox" class="custom-control-input"
                                                id="payment-{{ $payment->id }}" value="{{ $payment->id }}"
                                                wire:model="selectedIds">
                                            <label class="custom-control-label" for="payment-{{ $payment->id }}"></label>
                                        </div>
                                    @endcan
                                    <div class="min-width-0">
                                        <div class="small">{{ $payment->paid_at_display }}</div>
                                        <div class="text-muted small text-truncate">{{ $payment->reference_no ?: '—' }}</div>
                                        <span class="badge badge-{{ $payment->status_badge }} mt-1"
                                            title="{{ $payment->status_enum?->description() }}">
                                            {{ $payment->status_label }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <span class="small text-truncate">{{ $payment->bill?->issued_to_name }}</span><br>
                                <span class="text-muted small text-truncate">{{ $payment->user?->email }}</span>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="small text-truncate">{{ $payment->bill_reference }}</div>
                                <div class="text-muted small text-truncate">{{ $payment->bill_type_label }}</div>
                            </td>
                            <td class="align-middle text-right">
                                <span class="font-weight-medium">{{ $payment->amount_display }}</span>
                                <div class="text-muted small">{{ $payment->method_label }}</div>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="paymentActions{{ $payment->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="paymentActions{{ $payment->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#paymentViewModal" data-id="{{ $payment->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Details
                                        </a>
                                        @include('livewire.admin.collections.payments.partials.receipt-actions', ['payment' => $payment])
                                        @if ($payment->is_recorded && ! $payment->journal_entry_id)
                                            @can('create', \App\Models\JournalEntry\JournalEntry::class)
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="postToJournal({{ $payment->id }})">
                                                    <i class="fas fa-fw fa-book mr-1"></i> Post to Journal
                                                </a>
                                            @endcan
                                        @endif
                                        @if ($payment->is_pending)
                                            @can('approve', \App\Models\Payment::class)
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#paymentApproveModal" data-id="{{ $payment->id }}">
                                                    <i class="fas fa-fw fa-check-circle mr-1"></i> Approve
                                                </a>
                                            @endcan
                                        @endif
                                        @can('delete', \App\Models\Payment::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#paymentDeleteModal" data-id="{{ $payment->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterMethod || $filterMonth
                                        ? 'No payments match your filters.'
                                        : 'No payments recorded yet.' }}
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
        @forelse ($payments as $payment)
            <div class="card mb-3 shadow-sm" wire:key="payment-card-{{ $payment->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Payment::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-payment-{{ $payment->id }}" value="{{ $payment->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-payment-{{ $payment->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $payment->reference_no ?: '—' }}
                                </div>
                                <div class="small text-muted">{{ $payment->paid_at_display }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $payment->status_badge }} flex-shrink-0">{{ $payment->status_label }}</span>
                        <span class="badge badge-secondary flex-shrink-0">{{ $payment->method_label }}</span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $payment->property_label }}</div>
                    <div class="text-muted small text-truncate mb-2">
                        {{ $payment->bill_reference }} &middot; {{ $payment->bill_type_label }}
                    </div>

                    <div>
                        <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                        <div class="font-weight-medium">{{ $payment->amount_display }}</div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobPaymentActions{{ $payment->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobPaymentActions{{ $payment->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#paymentViewModal" data-id="{{ $payment->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> View Details
                            </a>
                            @include('livewire.admin.collections.payments.partials.receipt-actions', ['payment' => $payment])
                            @if ($payment->is_recorded && ! $payment->journal_entry_id)
                                @can('create', \App\Models\JournalEntry\JournalEntry::class)
                                    <a class="dropdown-item" href="#"
                                        wire:click.prevent="postToJournal({{ $payment->id }})">
                                        <i class="fas fa-fw fa-book mr-1"></i> Post to Journal
                                    </a>
                                @endcan
                            @endif
                            @if ($payment->is_pending)
                                @can('approve', \App\Models\Payment::class)
                                    <a class="dropdown-item" href="#" data-toggle="modal"
                                        data-target="#paymentApproveModal" data-id="{{ $payment->id }}">
                                        <i class="fas fa-fw fa-check-circle mr-1"></i> Approve
                                    </a>
                                @endcan
                            @endif
                            @can('delete', \App\Models\Payment::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#paymentDeleteModal" data-id="{{ $payment->id }}">
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
                    <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterMethod || $filterMonth
                            ? 'No payments match your filters.'
                            : 'No payments recorded yet.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($payments->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $payments->links() }}
        </div>
    @endif

</div>
