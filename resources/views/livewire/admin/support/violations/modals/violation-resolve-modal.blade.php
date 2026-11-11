<div wire:ignore.self>
    <x-adminlte-modal id="violationResolveModal" title="Resolve Violation"
        icon="fas fa-fw fa-check-circle" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the violation…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($violation)
                {{-- What is being closed --}}
                <div class="font-weight-bold mb-0">{{ $violation->type_label }}</div>
                <div class="text-muted small">
                    {{ $violation->resident_label }} · {{ $violation->occurred_display }}
                </div>
                <div class="text-muted small mb-3">
                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>{{ $violation->location }}
                </div>

                {{-- The optional note on how it was settled --}}
                <x-adminlte-textarea name="resolutionNotes" label="Resolution Notes (optional)" rows="3"
                    placeholder="How it was settled — what the homeowner did, what was agreed, anything worth keeping on record."
                    wire:model.defer="resolutionNotes" igroup-size="sm" />
                @error('resolutionNotes') <span class="text-danger small">{{ $message }}</span> @enderror

                {{-- What resolving does, said plainly --}}
                <div class="alert alert-info mb-0 small">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>
                    Resolving takes this violation off the homeowner's <strong>current</strong> violations and keeps it
                    permanently in their <strong>violation history</strong>. Nothing is deleted.
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Resolve Violation" theme="success" icon="fas fa-fw fa-check"
                wire:click="resolve" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="resolve" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Resolving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
