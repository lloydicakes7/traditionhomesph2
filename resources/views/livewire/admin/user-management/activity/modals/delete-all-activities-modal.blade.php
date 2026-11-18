<div wire:ignore.self>
    <x-adminlte-modal id="deleteAllActivitiesModal"
        title="Delete All Activity Logs"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading activity log count…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-2">
                Are you sure you want to permanently delete
                <strong>{{ $activityCount }}</strong>
                {{ $activityCount === 1 ? 'activity log record' : 'activity log records' }}?
            </p>

            <p class="text-danger small mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                This permanently deletes all activity log records. User accounts, roles, and other
                records are not affected. This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete All Logs" theme="danger" icon="fas fa-fw fa-trash-alt"
                wire:click="deleteAll" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded || $activityCount === 0" />
            <span wire:loading wire:target="deleteAll" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
