<div wire:ignore.self>
    <x-adminlte-modal id="postToJournalModal" title="Post to Journal"
        icon="fas fa-fw fa-book" theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading posting…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
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
                        placeholder="What is this entry for?"
                        wire:model.defer="memo" igroup-size="sm" />
                </div>
            </div>

            @include('livewire.admin.accounting.partials.journal-entry-lines')

            @error('posting')
                <div class="alert alert-danger py-2 px-3 small mt-3 mb-0">{{ $message }}</div>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Post entry" theme="primary" icon="fas fa-fw fa-save"
                wire:click="confirm" wire:loading.attr="disabled" wire:target="confirm" class="btn-sm"
                :disabled="! $loaded" />

            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Posting…
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
