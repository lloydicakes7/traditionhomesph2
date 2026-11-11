<div wire:ignore.self>
    <x-adminlte-modal id="journalEntryViewModal"
        title="{{ $entry ? 'Journal Entry: ' . $entry->entry_no : 'Journal Entry' }}"
        icon="fas fa-fw fa-book" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading entry…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($entry)
                <div class="mb-2">
                    <span class="badge badge-{{ $entry->status_badge }}">{{ $entry->status_label }}</span>
                    <span class="badge badge-{{ $entry->classification ? $entry->classification->badgeTheme() : 'secondary' }}">
                        {{ $entry->classification_label }}
                    </span>
                </div>

                <div class="border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-muted small text-uppercase">Date</div>
                            <div class="font-weight-medium">{{ $entry->entry_date_display }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small text-uppercase">Source</div>
                            <div class="font-weight-medium">{{ $entry->source_label }}</div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-6">
                            <div class="text-muted small text-uppercase">OR / Reference #</div>
                            <div><x-ui.value :value="$entry->reference_no" /></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small text-uppercase">Recorded By</div>
                            <div>{{ $entry->postedByAdmin ? $entry->postedByAdmin->name : 'System' }}</div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="text-muted small text-uppercase">Memo</div>
                        <div><x-ui.value :value="$entry->memo" /></div>
                    </div>
                </div>

                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Code</th>
                            <th style="width: 40%;">Account</th>
                            <th class="text-right" style="width: 24%;">To</th>
                            <th class="text-right" style="width: 24%;">From</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td><x-ui.value :value="$line->account ? $line->account->code : null" /></td>
                                <td>{{ $line->account_name }}</td>
                                <td class="text-right">
                                    <x-ui.value :value="$line->isDebit() ? $line->amount_display : null" />
                                </td>
                                <td class="text-right">
                                    <x-ui.value :value="$line->isDebit() ? null : $line->amount_display" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2" class="text-right font-weight-bold">Entry Total</td>
                            <td colspan="2" class="text-right font-weight-bold">
                                {{ \App\Utility\MoneyHelper::format($entry->total_amount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <p class="small {{ $entry->isBalanced() ? 'text-success' : 'text-danger' }} mb-0">
                    <i class="fas fa-fw fa-{{ $entry->isBalanced() ? 'check-circle' : 'exclamation-triangle' }}"></i>
                    {{ $entry->isBalanced()
                        ? 'What goes to the accounts equals what comes from them.'
                        : 'This entry is out of balance.' }}
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            @if ($entry)
                <a href="{{ route('accounting.journal-entries.voucher', $entry->id) }}" target="_blank"
                    class="btn btn-sm btn-primary">
                    <i class="fas fa-fw fa-print mr-1"></i> Print Voucher
                </a>
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
