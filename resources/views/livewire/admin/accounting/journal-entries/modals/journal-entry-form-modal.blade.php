<div wire:ignore.self>
    <x-adminlte-modal id="journalEntryFormModal"
        title="{{ $isEditing ? 'Edit Journal Entry' : 'New Journal Entry' }}"
        icon="fas fa-fw fa-book" theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading entry form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($isEditing)
                <div class="alert alert-light border py-2 px-3 small">
                    <i class="fas fa-fw fa-pen mr-1 text-primary"></i>
                    Editing <span class="font-weight-bold">{{ $editingEntryLabel }}</span>
                    @if ($isSystemEntry)
                        <div class="text-muted mt-1">
                            This entry came from a {{ strtolower($editingSourceLabel) }}.
                            Its lines stay as posted.
                        </div>
                    @endif
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-select name="classification" label="Classification"
                        wire:model="classification" igroup-size="sm">
                        @foreach ($classifications as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>

                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="entry_date" label="Date" type="date"
                        wire:model.defer="entry_date" igroup-size="sm" />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <x-adminlte-input name="reference_no" label="OR / Reference #"
                        placeholder="Optional" wire:model.defer="reference_no" igroup-size="sm" />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <x-adminlte-textarea name="memo" label="Memo" rows="2"
                        placeholder="What is this entry for? e.g., Corrected June dues posting"
                        wire:model.defer="memo" igroup-size="sm" />
                </div>
            </div>

            @if ($isSystemEntry)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 56%;">Account</th>
                                <th class="text-right" style="width: 22%;">To</th>
                                <th class="text-right" style="width: 22%;">From</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lines as $line)
                                <tr>
                                    <td><x-ui.value :value="$accountOptions[$line['account_id']] ?? (($line['account_name'] ?? '') ?: null)" /></td>
                                    <td class="text-right">
                                        <x-ui.value :value="$line['side'] === 'debit' ? \App\Utility\MoneyHelper::format($line['amount']) : null" />
                                    </td>
                                    <td class="text-right">
                                        <x-ui.value :value="$line['side'] === 'credit' ? \App\Utility\MoneyHelper::format($line['amount']) : null" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                @include('livewire.admin.accounting.partials.journal-entry-lines')
            @endif

            @if ($isEditing && ! $isSystemEntry && $confirmingRemove)
                <div class="alert alert-danger mb-0 mt-3">
                    <div class="font-weight-bold mb-1">Remove {{ $editingEntryLabel }}?</div>
                    <p class="small mb-2">
                        The entry and its lines will be deleted from the books. This can't be undone.
                    </p>
                    <x-adminlte-button label="Yes, remove entry" theme="danger" icon="fas fa-fw fa-trash-alt"
                        wire:click="removeEntry" wire:loading.attr="disabled" class="btn-sm" />
                    <x-adminlte-button label="Keep entry" theme="secondary"
                        wire:click="$set('confirmingRemove', false)" class="btn-sm" />
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            @if ($isEditing && ! $isSystemEntry && ! $confirmingRemove)
                <x-adminlte-button label="Remove" theme="outline-danger" icon="fas fa-fw fa-trash-alt"
                    wire:click="$set('confirmingRemove', true)" class="btn-sm mr-auto" />
            @endif

            <span wire:loading wire:target="save" class="text-info small mr-2">
                <i class="fas fa-spinner fa-spin"></i> {{ $isEditing ? 'Saving…' : 'Posting…' }}
            </span>

            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Save changes' : 'Post entry' }}"
                theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" wire:target="save" class="btn-sm"
                :disabled="! $loaded" />
        </x-slot>
    </x-adminlte-modal>
</div>
