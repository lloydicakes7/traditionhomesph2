<div wire:ignore.self>
    <x-adminlte-modal id="announcementDeleteModal"
        title="Delete Announcement"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading announcement…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingAnnouncementData['title'] ?? '' }}</div>
            @if (! empty($deletingAnnouncementData['date']))
                <div class="text-muted small">
                    <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $deletingAnnouncementData['date'] }}
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                This removes the post from the HOA Center and the public home page announcement list.
                @if (! empty($deletingAnnouncementData['has_image'])) Along with its image, @endif
                This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" :disabled="! $loaded" class="btn-sm" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
