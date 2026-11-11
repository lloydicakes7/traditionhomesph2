<div wire:ignore.self>
    <x-adminlte-modal id="requestCompleteModal" title="Complete Request"
        icon="fas fa-fw fa-flag-checkered" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                <div class="font-weight-bold mb-0 text-break">{{ $request->type_label }}</div>
                <div class="text-muted small text-break">
                    {{ $request->resident_label }}
                    @if ($request->summary !== '—') · {{ $request->summary }} @endif
                </div>
                <div class="text-muted small mb-3">
                    Filed {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : '—' }}
                </div>

                <p class="mb-0">
                    Completing closes this request as done. Confirm once the issued document has been
                    handed over — nothing else is left to do on it.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Complete Request" theme="primary" icon="fas fa-fw fa-flag-checkered"
                wire:click="complete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="complete" class="ml-2 text-primary small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
