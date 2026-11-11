<div wire:ignore.self>
    <x-adminlte-modal id="complaintDeleteModal" title="Delete Complaint"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingComplaintData['subject'] ?? '' }}</div>
            @if (! empty($deletingComplaintData['category']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-tag mr-1"></i>{{ $deletingComplaintData['category'] }}
                </div>
            @endif
            @if (! empty($deletingComplaintData['submitter']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-user mr-1"></i>Submitted by {{ $deletingComplaintData['submitter'] }}
                    @if (! empty($deletingComplaintData['status']))
                        · currently {{ $deletingComplaintData['status'] }}
                    @endif
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                This removes the complaint and the office's response to it. A complaint the office has already acted
                on is usually better <strong>closed</strong> than deleted, so the record of what was reported stays on
                file. This action cannot be undone.
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
