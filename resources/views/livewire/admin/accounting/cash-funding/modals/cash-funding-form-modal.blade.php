<div wire:ignore.self>
    <x-adminlte-modal id="cashFundingFormModal"
        title="Add Cash to Account" icon="fas fa-fw fa-plus-circle" theme="primary"
        scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading cash funding form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="entry_date" label="Date" type="date"
                        wire:model.defer="entry_date" igroup-size="sm" />
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="amount" label="Amount" type="number" step="0.01" min="0"
                        placeholder="0.00" wire:model.defer="amount" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>

            <x-adminlte-select2 id="cashFundingToAccount" name="to_account_id" label="To Account (cash is added here)"
                wire:model.defer="to_account_id" igroup-size="sm"
                :config="['placeholder' => 'Choose the account the cash was added to', 'width' => '100%']">
                <option value="">Choose the account the cash was added to</option>
                @foreach ($accounts as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select2>

            <x-adminlte-input name="reference_no" label="OR / Reference #"
                placeholder="Bank confirmation or document number (optional)"
                wire:model.defer="reference_no" igroup-size="sm" />

            <x-adminlte-input name="memo" label="Memo"
                placeholder="e.g., Cash the association already held outside the books"
                wire:model.defer="memo" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Add Cash" theme="primary"
                icon="fas fa-fw fa-plus-circle" wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Posting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
