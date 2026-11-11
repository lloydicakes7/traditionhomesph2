<div wire:ignore.self>
    <x-adminlte-modal id="transferFormModal"
        title="Record Transfer" icon="fas fa-fw fa-exchange-alt" theme="primary"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading transfer form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="transfer_date" label="Date" type="date"
                        wire:model.defer="transfer_date" igroup-size="sm" />
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="amount" label="Amount" type="number" step="0.01" min="0"
                        placeholder="0.00" wire:model.defer="amount" igroup-size="sm" />
                </div>
            </div>

            <x-adminlte-select2 id="transferFromAccount" name="from_account_id" label="From Account (money leaves)"
                wire:model.defer="from_account_id" igroup-size="sm"
                :config="['placeholder' => 'Choose the account the money left', 'width' => '100%']">
                <option value="">Choose the account the money left</option>
                @foreach ($accounts as $id => $label)
                    <option value="{{ $id }}">{{ $label }}@isset($balances[$id]) — {{ $balances[$id] }}@endisset</option>
                @endforeach
            </x-adminlte-select2>

            <x-adminlte-select2 id="transferToAccount" name="to_account_id" label="To Account (money arrives)"
                wire:model.defer="to_account_id" igroup-size="sm"
                :config="['placeholder' => 'Choose the account the money arrived in', 'width' => '100%']">
                <option value="">Choose the account the money arrived in</option>
                @foreach ($accounts as $id => $label)
                    <option value="{{ $id }}">{{ $label }}@isset($balances[$id]) — {{ $balances[$id] }}@endisset</option>
                @endforeach
            </x-adminlte-select2>

            <x-adminlte-input name="memo" label="Memo"
                placeholder="e.g., Fund the operating account"
                wire:model.defer="memo" igroup-size="sm" />

            <x-adminlte-input name="reference_no" label="Reference No."
                placeholder="Bank confirmation or document number (optional)"
                wire:model.defer="reference_no" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Record Transfer" theme="primary"
                icon="fas fa-fw fa-exchange-alt" wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Posting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
