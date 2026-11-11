<div wire:ignore.self>
    <x-adminlte-modal id="activityDeleteModal"
        title="Delete Activity Log"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the activity log…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-1">Are you sure you want to delete this activity log?</p>

            @if ($deletingActivityId)
                <p class="text-muted small mb-1">
                    <span class="badge badge-{{ $deletingActivityTheme }}">{{ $deletingActivityLabel }}</span>
                    &mdash; {{ $deletingActivityDate }}
                </p>
                <p class="mb-0">{{ $deletingActivityDescription }}</p>
            @endif

            <p class="text-danger small mt-2 mb-0">
                <i class="fas fa-fw fa-info-circle"></i> This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Log" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
