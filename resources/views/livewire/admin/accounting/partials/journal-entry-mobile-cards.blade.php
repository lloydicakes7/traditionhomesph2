{{-- =================================================================== --}}
{{-- MOBILE CARDS                                                         --}}
{{-- =================================================================== --}}
<div class="d-block d-lg-none">
    @forelse ($entries as $entry)
        <div class="card mb-3 shadow-sm" wire:key="journal-card-{{ $entry->id }}">
            <div class="card-header bg-white px-3 py-2">
                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                    {{ $entry->entry_no }}
                </div>
                <div class="small text-muted">{{ $entry->entry_date_display }}</div>
            </div>
            <div class="card-body px-3 py-3">
                <div class="small text-truncate"><x-ui.value :value="$entry->memo" /></div>
                <div class="text-muted small mb-2">
                    <span class="badge badge-{{ $entry->classification ? $entry->classification->badgeTheme() : 'secondary' }}">
                        {{ $entry->classification_label }}
                    </span>
                    <span class="badge badge-{{ $entry->source_badge }}">{{ $entry->source_label }}</span>
                    @if ($entry->reference_no)
                        &middot; Ref. {{ $entry->reference_no }}
                    @endif
                </div>

                <div class="d-flex justify-content-end">
                    <span class="font-weight-medium">
                        {{ \App\Utility\MoneyHelper::format($entry->total_amount) }}
                    </span>
                </div>
            </div>
            <div class="card-footer bg-light py-2 text-right">
                <div class="btn-group btn-group-sm" role="group" aria-label="Actions">
                    <x-adminlte-button label="View" theme="info" icon="fas fa-fw fa-eye"
                        data-toggle="modal" data-target="#journalEntryViewModal" data-id="{{ $entry->id }}" class="btn-xs" />
                    <a href="{{ route('accounting.journal-entries.voucher', $entry->id) }}"
                        class="btn btn-xs btn-secondary" title="Print Journal Voucher" aria-label="Print Journal Voucher">
                        <i class="fas fa-fw fa-print"></i>
                    </a>
                    @can('update', \App\Models\JournalEntry\JournalEntry::class)
                        @if (! $entry->status || ! $entry->status->is(\App\Enums\JournalEntryStatus::VOID()))
                            <x-adminlte-button label="Edit" theme="primary" icon="fas fa-fw fa-edit"
                                data-toggle="modal" data-target="#journalEntryFormModal" data-id="{{ $entry->id }}" class="btn-xs" />
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-fw fa-book fa-2x mb-2"></i>
                <p class="mb-0">
                    {{ $search || $filterSource || $filterClassification || $filterPeriod
                        ? 'No journal entries match your filters.'
                        : 'The books are empty.' }}
                </p>
            </div>
        </div>
    @endforelse
</div>
