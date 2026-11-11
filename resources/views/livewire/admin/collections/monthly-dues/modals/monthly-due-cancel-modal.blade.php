<div wire:ignore.self>
    <x-adminlte-modal id="dueCancelModal" title="Cancel Monthly Due"
        icon="fas fa-fw fa-times-circle" theme="warning" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading monthly due…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Cancel <strong>{{ $cancellingDueLabel }}</strong>?</p>
            <p class="text-muted small mb-0">
                This property's dues for the month are withdrawn — nothing more is collectible on them.
                Payments already recorded stay on the ledger.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Keep Bill" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Cancel Monthly Due" theme="warning" icon="fas fa-fw fa-times-circle"
                wire:click="cancel" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="cancel" class="ml-2 text-warning small">
                <i class="fas fa-spinner fa-spin"></i> Cancelling...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
