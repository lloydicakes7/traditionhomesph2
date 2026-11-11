<div wire:ignore.self>
    {{-- The shell is always rendered: Livewire never adds or removes the modal
         element, so Bootstrap's own show/hide lifecycle — and the backdrop that
         goes with it — is never interrupted mid-flight. --}}
    <x-adminlte-modal id="requestDeleteModal" title="Delete Request"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="font-weight-bold mb-0">{{ $deletingRequestData['type'] ?? '' }}</div>
            @if (! empty($deletingRequestData['resident']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-user mr-1"></i>{{ $deletingRequestData['resident'] }}
                    @if (! empty($deletingRequestData['summary']) && $deletingRequestData['summary'] !== '—')
                        · {{ $deletingRequestData['summary'] }}
                    @endif
                </div>
            @endif
            @if (! empty($deletingRequestData['status']))
                <div class="text-muted small">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>Currently {{ $deletingRequestData['status'] }}
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                Deleting is for a request filed in error — it removes the record entirely. A request the office
                has already acted on is usually better left standing, so the outcome stays on the submitter's
                record. This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            {{-- Kept from the first paint, only disabled until loaded: Livewire binds
                 wire:click on a node it ADDS, so a button introduced by the load round
                 trip would morph onto this <button> and never get a listener. --}}
            <x-adminlte-button label="Delete" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
