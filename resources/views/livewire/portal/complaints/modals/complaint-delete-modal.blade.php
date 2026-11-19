<div wire:ignore.self>
    <x-adminlte-modal id="complaintDeleteModal" title="Delete Complaint"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($deletingComplaintData)
                <div class="font-weight-bold text-break mb-0">{{ $deletingComplaintData['subject'] }}</div>
                <div class="text-muted small">
                    <i class="fas fa-fw fa-tag mr-1"></i>{{ $deletingComplaintData['category'] }}
                    · currently {{ $deletingComplaintData['status'] }}
                </div>

                <p class="text-danger small mt-3 mb-0">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>
                    This removes the complaint and any attachments you filed with it. This action cannot be undone.
                </p>

                @error('delete')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Keep Complaint" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Complaint" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
