<div wire:ignore.self>
    <x-adminlte-modal id="advanceDepositFormModal" title="Add Advance Deposit"
        icon="fas fa-fw fa-piggy-bank" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading advance deposit form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <x-adminlte-select2 id="depositUserSelect" name="form.user_id" label="Homeowner"
                wire:model="form.user_id" igroup-size="sm"
                :config="['placeholder' => 'Select the homeowner the credit is held for…', 'width' => '100%']">
                <option value=""></option>
                @foreach ($users as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select2>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="form.amount" label="Amount Received" type="number" step="0.01" min="0.01"
                        wire:model.lazy="form.amount" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="form.deposited_at" label="Date Received" type="date"
                        wire:model.defer="form.deposited_at" igroup-size="sm" />
                </div>
            </div>

            <x-adminlte-textarea name="form.notes" label="Notes" rows="2"
                wire:model.defer="form.notes" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Record Deposit" theme="primary" icon="fas fa-fw fa-save"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
