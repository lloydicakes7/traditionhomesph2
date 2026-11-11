<div wire:ignore.self>
    <x-adminlte-modal id="advanceDepositRefundModal" title="Refund Advance Deposit"
        icon="fas fa-fw fa-undo" theme="warning" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading advance deposit…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Refund <strong>{{ $refundingDepositLabel }}</strong>?</p>
            <p class="text-muted small mb-0">
                What is left of the credit is returned to the homeowner and the deposit is closed.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Refund" theme="warning" icon="fas fa-fw fa-undo"
                wire:click="refund" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="refund" class="ml-2 text-warning small">
                <i class="fas fa-spinner fa-spin"></i> Refunding...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
