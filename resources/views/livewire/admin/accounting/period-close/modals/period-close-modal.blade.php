<div wire:ignore.self>
    <x-adminlte-modal id="periodCloseModal"
        title="Close the Books" icon="fas fa-fw fa-lock" theme="dark"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading month…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Close the books for <strong>{{ $closingPeriodLabel }}</strong>?</p>
            <p class="text-muted small">
                Once closed, nothing dated inside the month can be posted — balances, receipts, payouts, transfers and
                transfers all stop moving for it.
            </p>

            <div class="form-group mb-0">
                <label class="small" for="periodCloseNotes">Notes (optional)</label>
                <textarea class="form-control form-control-sm" id="periodCloseNotes" rows="2"
                    wire:model.defer="notes" placeholder="Why the month is being closed"></textarea>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Close Month" theme="dark" icon="fas fa-fw fa-lock"
                wire:click="closePeriod" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="closePeriod" class="ml-2 text-dark small">
                <i class="fas fa-spinner fa-spin"></i> Closing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
