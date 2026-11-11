<div>
    <x-adminlte-card>
        <div class="row justify-content-end align-items-center">
            <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                <x-adminlte-button label="Clear Filters" theme="secondary" icon="fas fa-fw fa-times"
                    wire:click="clearFilters" wire:loading.attr="disabled" class="btn-sm btn-block" />
            </div>
            <div class="col-12 col-sm-auto">
                @can('create', \App\Models\JournalEntry\JournalEntry::class)
                    <x-adminlte-button label="Add Cash" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#cashFundingFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    <div class="row">
        <div class="col-12 col-sm-6">
            <x-adminlte-small-box theme="primary" icon="fas fa-fw fa-circle-plus"
                text="Funding Entries Shown" title="{{ $entries->total() }}" />
        </div>
        <div class="col-12 col-sm-6">
            <x-adminlte-small-box theme="success" icon="fas fa-fw fa-coins"
                text="Total Added" title="{{ \App\Utility\MoneyHelper::format($totals['amount']) }}" />
        </div>
    </div>

    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-8 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search entry number, reference or memo..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-6 col-lg-4">
                <x-adminlte-select name="filterPeriod" wire:model="filterPeriod" igroup-size="sm" fgroup-class="mb-0">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>
    </x-adminlte-card>

    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="align-middle" style="width: 16%;">Date / Entry #</th>
                        <th class="align-middle" style="width: 26%;">To Account</th>
                        <th class="align-middle" style="width: 28%;">Memo</th>
                        <th class="align-middle text-right" style="width: 14%;">Amount</th>
                        <th class="text-center align-middle" style="width: 16%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-medium">{{ $entry->entry_no }}</div>
                                <div class="small text-muted">{{ $entry->entry_date_display }}</div>
                            </td>
                            <td class="align-middle">{{ $entry->goes_to }}</td>
                            <td class="align-middle">
                                <div class="text-truncate" style="max-width: 360px;"><x-ui.value :value="$entry->memo" /></div>
                                @if ($entry->reference_no)
                                    <div class="small text-muted">Ref. {{ $entry->reference_no }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-right font-weight-medium">
                                {{ \App\Utility\MoneyHelper::format($entry->total_amount) }}
                            </td>
                            <td class="align-middle text-center text-nowrap">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                        data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $entry->id }}"
                                        class="btn-xs" title="View Journal" />
                                    <a href="{{ route('accounting.journal-entries.voucher', $entry->id) }}"
                                        class="btn btn-xs btn-secondary" title="Print Journal Voucher">
                                        <i class="fas fa-fw fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-circle-plus fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterPeriod
                                        ? 'No cash funding entries match your filters.'
                                        : 'No cash funding yet — add cash the association already holds outside the books here.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-block d-lg-none">
        @forelse ($entries as $entry)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                {{ $entry->entry_no }}
                            </div>
                            <div class="small text-muted">{{ $entry->entry_date_display }}</div>
                        </div>
                        <span class="font-weight-medium flex-shrink-0">
                            {{ \App\Utility\MoneyHelper::format($entry->total_amount) }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small">
                        <i class="fas fa-fw fa-arrow-alt-circle-left text-success mr-1"></i>{{ $entry->goes_to }}
                    </div>
                    <div class="text-muted small text-truncate mt-1"><x-ui.value :value="$entry->memo" /></div>
                    @if ($entry->reference_no)
                        <div class="text-muted small">Ref. {{ $entry->reference_no }}</div>
                    @endif
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                        <x-adminlte-button label="View" theme="info" icon="fas fa-fw fa-eye"
                            data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $entry->id }}" class="btn-xs" />
                        <a href="{{ route('accounting.journal-entries.voucher', $entry->id) }}"
                            class="btn btn-xs btn-secondary" title="Print Journal Voucher">
                            <i class="fas fa-fw fa-print"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-circle-plus fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterPeriod
                            ? 'No cash funding entries match your filters.'
                            : 'No cash funding recorded yet.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($entries->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $entries->links() }}
        </div>
    @endif
</div>
