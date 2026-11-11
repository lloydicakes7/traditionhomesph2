<div wire:ignore.self>
    <x-adminlte-modal id="periodReopenModal"
        title="Reopen the Books" icon="fas fa-fw fa-lock-open" theme="warning"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading month…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-2">Reopen <strong>{{ $reopeningPeriodLabel }}</strong>?</p>

            <p class="text-warning small mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                Reopening puts a locked month back in play — postings may be made into it again, and the statements
                for it will move.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Reopen Month" theme="warning" icon="fas fa-fw fa-lock-open"
                wire:click="reopenPeriod" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="reopenPeriod" class="ml-2 text-warning small">
                <i class="fas fa-spinner fa-spin"></i> Reopening...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
