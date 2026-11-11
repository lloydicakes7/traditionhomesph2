<div>
    <x-adminlte-card>
        <div class="row justify-content-end align-items-center">
            <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                <x-adminlte-button label="Clear Filters" theme="secondary" icon="fas fa-fw fa-times"
                    wire:click="clearFilters" wire:loading.attr="disabled" class="btn-sm btn-block" />
            </div>
            <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                @can('export', \App\Models\JournalEntry\JournalEntry::class)
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-csv"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>
            <div class="col-12 col-sm-auto">
                @can('create', \App\Models\JournalEntry\JournalEntry::class)
                    <x-adminlte-button label="New Journal Entry" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#journalEntryFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- THE BOOKS AT A GLANCE                                                --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-sm-6">
            <x-adminlte-small-box theme="primary" icon="fas fa-fw fa-book"
                text="Entries Shown" title="{{ $entries->total() }}" />
        </div>
        <div class="col-12 col-sm-6">
            <x-adminlte-small-box theme="info" icon="fas fa-fw fa-coins"
                text="Total Amount" title="{{ \App\Utility\MoneyHelper::format($postedTotals['amount']) }}" />
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search by entry number, reference or memo..."
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

            <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-select name="filterClassification" wire:model="filterClassification" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Classifications</option>
                    @foreach ($classifications as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-6 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-select name="filterPeriod" wire:model="filterPeriod" igroup-size="sm" fgroup-class="mb-0">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- THE JOURNAL                                                          --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="cursor-pointer align-middle" wire:click="sortBy('entry_no')" style="width: 20%;">
                            Entry # / Date
                            @if ($sortField === 'entry_no')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th scope="col" class="align-middle" style="width: 38%;">Memo</th>
                        <th scope="col" class="align-middle" style="width: 16%;">Classification</th>
                        <th scope="col" class="align-middle text-right" style="width: 14%;">Amount</th>
                        <th scope="col" class="align-middle text-center" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr wire:key="journal-row-{{ $entry->id }}">
                            <td class="align-middle">
                                <div class="font-weight-medium">{{ $entry->entry_no }}</div>
                                <div class="small text-muted">{{ $entry->entry_date_display }}</div>
                            </td>
                            <td class="align-middle">
                                <div class="text-truncate" style="max-width: 360px;"><x-ui.value :value="$entry->memo" /></div>
                                <div class="small text-muted mt-1">
                                    <span class="badge badge-{{ $entry->source_badge }}">{{ $entry->source_label }}</span>
                                    @if ($entry->reference_no)
                                        <span class="ml-1">Ref. {{ $entry->reference_no }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $entry->classification ? $entry->classification->badgeTheme() : 'secondary' }}">
                                    {{ $entry->classification_label }}
                                </span>
                            </td>
                            <td class="align-middle text-right font-weight-medium">
                                {{ \App\Utility\MoneyHelper::format($entry->total_amount) }}
                            </td>
                            <td class="align-middle text-center text-nowrap">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                        data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $entry->id }}"
                                        class="btn-xs" title="View Journal" aria-label="View Journal" />
                                    <a href="{{ route('accounting.journal-entries.voucher', $entry->id) }}"
                                        class="btn btn-xs btn-secondary" title="Print Journal Voucher" aria-label="Print Journal Voucher">
                                        <i class="fas fa-fw fa-print"></i>
                                    </a>
                                    @can('update', \App\Models\JournalEntry\JournalEntry::class)
                                        @if (! $entry->status || ! $entry->status->is(\App\Enums\JournalEntryStatus::VOID()))
                                            <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                                data-toggle="modal" data-target="#journalEntryFormModal" data-id="{{ $entry->id }}"
                                                class="btn-xs" title="Edit Entry" aria-label="Edit Entry" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-book fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterSource || $filterClassification || $filterPeriod
                                        ? 'No journal entries match your filters.'
                                        : 'The books are empty — every payment and expense posts its own entry here.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('livewire.admin.accounting.partials.journal-entry-mobile-cards')

    @if ($entries->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $entries->links() }}
        </div>
    @endif
</div>
