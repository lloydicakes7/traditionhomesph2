<div>

    <x-adminlte-card>
        <div class="row justify-content-end align-items-center">
            <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                <x-adminlte-button label="Clear Filters" theme="secondary" icon="fas fa-fw fa-times"
                    wire:click="clearFilters" wire:loading.attr="disabled" class="btn-sm btn-block" />
            </div>
            @can('export', \App\Models\Account\AccountTransfer::class)
                <div class="col-12 col-sm-auto">
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-csv"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                </div>
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- WHAT MOVED: how many transfers, and how much money changed pockets   --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-4">
            <x-adminlte-small-box theme="primary" icon="fas fa-fw fa-exchange-alt"
                text="Transfers Shown" title="{{ $totals['count'] }}" />
        </div>
        <div class="col-6 col-lg-4">
            <x-adminlte-small-box theme="info" icon="fas fa-fw fa-money-bill-transfer"
                text="Amount Moved" title="{{ \App\Utility\MoneyHelper::format($totals['amount']) }}" />
        </div>
        <div class="col-12 col-lg-4">
            <x-adminlte-small-box theme="secondary" icon="fas fa-fw fa-info-circle"
                text="Not Income or Expense" title="Internal Only" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search reference, memo or account"
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

            <div class="col-12 col-sm-6 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-select name="filterAccount" wire:model="filterAccount" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Accounts</option>
                    @foreach ($accounts as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-lg-auto ml-lg-auto">
                @can('create', \App\Models\Account\AccountTransfer::class)
                    <x-adminlte-button label="Record Transfer" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#transferFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('reference')" style="width:20%;">
                            Reference / Date
                            @if ($sortField === 'reference')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:25%;">From</th>
                        <th class="align-middle" style="width:25%;">To</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('amount')" style="width:15%;">
                            Amount
                            @if ($sortField === 'amount')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $transfer->reference }}</div>
                                <div class="small text-muted text-truncate">{{ $transfer->transfer_date_display }}</div>
                                <div class="small text-muted text-truncate">{{ $transfer->memo }}</div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <span class="small text-truncate">{{ $transfer->from_label }}</span>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <span class="small text-truncate">{{ $transfer->to_label }}</span>
                            </td>
                            <td class="align-middle text-right font-weight-medium">{{ $transfer->amount_display }}</td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="transferActions{{ $transfer->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="transferActions{{ $transfer->id }}">
                                        @if ($transfer->journal_entry_id)
                                            @can('viewAny', \App\Models\JournalEntry\JournalEntry::class)
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#journalEntryViewModal" data-id="{{ $transfer->journal_entry_id }}">
                                                    <i class="fas fa-fw fa-eye mr-1"></i> View Journal
                                                </a>
                                            @endcan
                                        @endif
                                        @can('delete', \App\Models\Account\AccountTransfer::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#transferDeleteModal" data-id="{{ $transfer->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Remove
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-exchange-alt fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterMonth || $filterAccount
                                        ? 'No transfers match your filters.'
                                        : 'No transfers recorded yet — move money between the association\'s own accounts here.' }}
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
        @forelse ($transfers as $transfer)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                {{ $transfer->reference }}
                            </div>
                            <div class="small text-muted">{{ $transfer->transfer_date_display }}</div>
                        </div>
                        <span class="font-weight-medium flex-shrink-0">{{ $transfer->amount_display }}</span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">
                        <i class="fas fa-fw fa-arrow-alt-circle-right text-danger mr-1"></i>{{ $transfer->from_label }}
                    </div>
                    <div class="small text-truncate">
                        <i class="fas fa-fw fa-arrow-alt-circle-left text-success mr-1"></i>{{ $transfer->to_label }}
                    </div>
                    <div class="text-muted small text-truncate mt-1">{{ $transfer->memo }}</div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobTransferActions{{ $transfer->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobTransferActions{{ $transfer->id }}">
                            @if ($transfer->journal_entry_id)
                                @can('viewAny', \App\Models\JournalEntry\JournalEntry::class)
                                    <a class="dropdown-item" href="#" data-toggle="modal"
                                        data-target="#journalEntryViewModal" data-id="{{ $transfer->journal_entry_id }}">
                                        <i class="fas fa-fw fa-eye mr-1"></i> View Journal
                                    </a>
                                @endcan
                            @endif
                            @can('delete', \App\Models\Account\AccountTransfer::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#transferDeleteModal" data-id="{{ $transfer->id }}">
                                    <i class="fas fa-fw fa-trash mr-1"></i> Remove
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-exchange-alt fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterMonth || $filterAccount
                            ? 'No transfers match your filters.'
                            : 'No transfers recorded yet.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($transfers->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
