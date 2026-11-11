<div wire:ignore.self>
    <x-adminlte-modal id="deleteAccountModal" title="Are you sure to delete your Account?"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the confirmation…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="text-muted small mb-3">
                This action <strong>cannot</strong> be undone. All of your data will be permanently removed.
            </p>

            <x-adminlte-input
                name="deleteConfirmation"
                label="Please type your full name ({{ Auth::user()->name }}) to confirm:"
                type="text"
                placeholder="Type your full name..."
                wire:model.debounce.500ms="deleteConfirmation"
                igroup-size="sm"
                autocomplete="off">
            </x-adminlte-input>

            @if ($deleteNameError)
                <span class="text-danger small">
                    <i class="fas fa-fw fa-exclamation-circle"></i>
                    Name does not match. Please type your full name exactly.
                </span>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" />
            <x-adminlte-button label="Yes, Delete My Account" theme="danger"
                icon="fas fa-fw fa-trash" wire:click="delete"
                :disabled="empty($deleteConfirmation) || $deleteConfirmation !== Auth::user()->name" />
        </x-slot>
    </x-adminlte-modal>
</div>
