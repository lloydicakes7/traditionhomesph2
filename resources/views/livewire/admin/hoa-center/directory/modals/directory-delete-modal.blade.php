<div wire:ignore.self>
    <x-adminlte-modal id="directoryDeleteModal"
        title="Delete Directory Entry"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading directory entry…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingEntryData['name'] ?? '' }}</div>
            @if (! empty($deletingEntryData['role']))
                <div class="text-muted small">{{ $deletingEntryData['role'] }}</div>
            @endif

            <div class="mt-2">
                @if (! empty($deletingEntryData['group']))
                    <span class="badge badge-secondary">{{ $deletingEntryData['group'] }}</span>
                @endif
                @if (! empty($deletingEntryData['phones']))
                    <span class="text-muted small ml-1">{{ $deletingEntryData['phones'] }}</span>
                @endif
            </div>

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i> This removes the entry and its phone numbers. This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
