<div wire:ignore.self>
    <x-adminlte-modal id="userDeleteModal"
        title="Delete Admin"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the account…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($deletingAdminName)
                <div class="font-weight-bold mb-0">{{ $deletingAdminName }}</div>
            @endif
            @if ($deletingAdminEmail)
                <div class="text-muted small mb-0">{{ $deletingAdminEmail }}</div>
            @endif

            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                An officer who has left is usually disabled instead. The account
                keeps its records, but stops being able to sign in.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Admin" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
