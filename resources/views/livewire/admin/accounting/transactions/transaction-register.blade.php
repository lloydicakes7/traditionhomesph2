<div>
    <x-adminlte-card>
        <div class="row justify-content-end align-items-center">
            <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                <x-adminlte-button label="Clear Filters" theme="secondary" icon="fas fa-fw fa-times"
                    wire:click="clearFilters" wire:loading.attr="disabled" class="btn-sm btn-block" />
            </div>
            <div class="col-12 col-sm-auto">
                @can('transaction.export')
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-csv"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>


    {{-- =================================================================== --}}
    {{-- THE MOVEMENTS AT A GLANCE                                            --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="primary" icon="fas fa-fw fa-list-ol"
                text="Movements Shown" title="{{ $totals['count'] }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="info" icon="fas fa-fw fa-arrow-down"
                text="Total Debits" title="{{ \App\Utility\MoneyHelper::format($totals['debits']) }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="info" icon="fas fa-fw fa-arrow-up"
                text="Total Credits" title="{{ \App\Utility\MoneyHelper::format($totals['credits']) }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="{{ $totals['debits'] === $totals['credits'] ? 'success' : 'danger' }}"
                icon="fas fa-fw fa-balance-scale" text="Register"
                title="{{ $totals['debits'] === $totals['credits'] ? 'Balanced' : 'Out of balance' }}" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search entry, reference, memo or account"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-6 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterSource" wire:model="filterSource" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Sources</option>
                    @foreach ($sources as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-6 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterAccount" wire:model="filterAccount" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Accounts</option>
                    @foreach ($accounts as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-6 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterMonth" wire:model="filterMonth" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Months</option>
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="cursor-pointer align-middle" style="width:16%;" wire:click="sortBy('entry_date')">
                            Date / Entry #
                            @if ($sortField === 'entry_date')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:38%;">Description</th>
                        <th class="cursor-pointer align-middle" style="width:22%;" wire:click="sortBy('account')">
                            Account
                            @if ($sortField === 'account')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-right" style="width:14%;" wire:click="sortBy('amount')">
                            Amount
                            @if ($sortField === 'amount')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines as $line)
                        <tr>
                            <td class="align-middle">
                                <div class="small">
                                    <x-ui.value :value="$line->entry && $line->entry->entry_date ? $line->entry->entry_date->format('M j, Y') : null" />
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 140px;">
                                    <x-ui.value :value="$line->entry ? $line->entry->entry_no : null" />
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="text-truncate" style="max-width: 320px;">
                                    <x-ui.value :value="$line->memo ?: ($line->entry ? $line->entry->memo : null)" />
                                </div>
                                <div class="small text-muted mt-1">
                                    @if ($line->entry)
                                        <span class="badge badge-{{ $line->entry->source_badge }}">{{ $line->entry->source_label }}</span>
                                        @if ($line->entry->reference_no)
                                            <span class="ml-1">Ref. {{ $line->entry->reference_no }}</span>
                                        @endif
                                    @else
                                        <x-ui.value :value="null" />
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="text-truncate d-inline-block" style="max-width: 260px;">
                                    {{ $line->account ? $line->account->label : $line->account_name }}
                                </span>
                            </td>
                            <td class="align-middle text-right font-weight-medium">
                                {{ \App\Utility\MoneyHelper::format($line->amount) }}
                                <div class="small text-muted">{{ $line->isDebit() ? 'goes to this account' : 'comes from this account' }}</div>
                            </td>
                            <td class="align-middle text-center">
                                @if ($line->entry)
                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                        data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $line->entry->id }}"
                                        class="btn-xs" title="Read the whole posting" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-list-ol fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterSource || $filterAccount || $filterMonth
                                        ? 'No movements match your filters.'
                                        : 'The register is empty — every charge, receipt, payout and transfer posts here.' }}
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
        @forelse ($lines as $line)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate" style="font-size:0.9rem;">
                                {{ $line->account ? $line->account->label : $line->account_name }}
                            </div>
                            <div class="small text-muted">
                                <x-ui.value :value="$line->entry && $line->entry->entry_date ? $line->entry->entry_date->format('M j, Y') : null" />
                                &middot; <x-ui.value :value="$line->entry ? $line->entry->entry_no : null" />
                            </div>
                        </div>
                        @if ($line->entry)
                            <span class="badge badge-{{ $line->entry->source_badge }} flex-shrink-0">{{ $line->entry->source_label }}</span>
                        @else
                            <x-ui.value :value="null" />
                        @endif
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate mb-2">
                        <x-ui.value :value="$line->memo ?: ($line->entry ? $line->entry->memo : null)" />
                    </div>
                    <div class="d-flex justify-content-between align-items-start" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">
                                {{ $line->isDebit() ? 'goes to' : 'comes from' }}
                            </div>
                            <div class="font-weight-medium">{{ \App\Utility\MoneyHelper::format($line->amount) }}</div>
                        </div>
                        @if ($line->entry)
                            <x-adminlte-button label="View" theme="info" icon="fas fa-fw fa-eye"
                                data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $line->entry->id }}" class="btn-xs" />
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-list-ol fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterSource || $filterAccount || $filterMonth
                            ? 'No movements match your filters.'
                            : 'The register is empty.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($lines->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $lines->links() }}
        </div>
    @endif
</div>
