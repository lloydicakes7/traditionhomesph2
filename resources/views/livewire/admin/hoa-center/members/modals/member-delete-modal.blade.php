<div wire:ignore.self>
    <x-adminlte-modal id="memberDeleteModal"
        title="Delete HOA Member"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading member…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingMemberData['name'] ?? '' }}</div>
            @if (! empty($deletingMemberData['position']))
                <div class="text-muted small">{{ $deletingMemberData['position'] }}</div>
            @endif

            @if (! empty($deletingMemberData['phone']))
                <div class="mt-2">
                    <span class="text-muted small"><i class="fas fa-fw fa-phone-alt mr-1"></i>{{ $deletingMemberData['phone'] }}</span>
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                This removes the HOA member from the Public and this page.
                @if (! empty($deletingMemberData['has_photo'])) Along with their photo, @endif
                This action cannot be undone.
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
