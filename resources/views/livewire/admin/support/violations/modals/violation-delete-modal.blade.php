<div wire:ignore.self>
    <x-adminlte-modal id="violationDeleteModal" title="Delete Violation"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the violation…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingViolationData['type'] ?? '' }}</div>
            @if (! empty($deletingViolationData['member']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-user mr-1"></i>{{ $deletingViolationData['member'] }}
                    @if (! empty($deletingViolationData['when']))
                        · {{ $deletingViolationData['when'] }}
                    @endif
                </div>
            @endif
            @if (! empty($deletingViolationData['status']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>Currently {{ $deletingViolationData['status'] }}
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                Deleting is for a violation issued in error — it removes the record from the homeowner's history
                entirely. To close a violation the office has acted on, <strong>resolve</strong> it instead: that keeps
                the history. This action cannot be undone.
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
