<div wire:ignore.self>
    <x-adminlte-modal id="roleDeleteModal"
        title="Delete Role"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the role…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-1">Are you sure you want to delete this role?</p>

            @if ($deletingRoleName)
                <div class="font-weight-bold mb-0">{{ $deletingRoleName }}</div>
            @endif
            @if ($deletingRoleDescription)
                <div class="text-muted small mb-0">{{ $deletingRoleDescription }}</div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Role" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
