<div wire:ignore.self>
    <x-adminlte-modal id="paymentMassDeleteModal" title="Delete Selected Payments"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading selection…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Are you sure you want to delete the <strong>{{ count($selectedIds) }}</strong> selected payments?</p>
            <p class="text-muted small mb-0">
                Each bill they settled reopens, and any advance-deposit credit they drew is returned.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="deleteMass" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="deleteMass" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
