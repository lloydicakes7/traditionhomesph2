<div wire:ignore.self>
    <x-adminlte-modal id="expenseApproveModal"
        title="Approve Expense"
        icon="fas fa-fw fa-clipboard-check" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading expense…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Approve expense <strong>{{ $approvingExpenseLabel }}</strong>?</p>

            <p class="text-muted small mb-0">
                The expense is signed off and locked. Accounting posts it to the books separately.
            </p>

            @error('approve')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Approve" theme="success" icon="fas fa-fw fa-clipboard-check"
                wire:click="approve" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="approve" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Approving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
