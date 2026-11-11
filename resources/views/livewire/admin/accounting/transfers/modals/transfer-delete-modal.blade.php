<div wire:ignore.self>
    <x-adminlte-modal id="transferDeleteModal"
        title="Remove Transfer" icon="fas fa-fw fa-exclamation-triangle" theme="danger"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading transfer…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-2">Remove transfer <strong>{{ $deletingTransferLabel }}</strong>?</p>

            <p class="text-danger small mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                The posting behind it is voided, not dropped — the ledger still says what was moved.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Remove" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Removing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
