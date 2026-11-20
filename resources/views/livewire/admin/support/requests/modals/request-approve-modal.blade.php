<div wire:ignore.self>
    <x-adminlte-modal id="requestApproveModal" title="Approve Request"
        icon="fas fa-fw fa-check-circle" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                <div class="font-weight-bold mb-0 text-break">{{ $request->type_label }}</div>
                <div class="text-muted small text-break">
                    {{ $request->resident_label }}
                    @if ($request->summary !== 'Not recorded') · {{ $request->summary }} @endif
                </div>
                <div class="text-muted small mb-3">
                    Filed {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : 'Not recorded' }}
                </div>

                <x-adminlte-input name="claimingAt" type="datetime-local"
                    label="Claiming Date and Time"
                    wire:model.defer="claimingAt" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="far fa-fw fa-calendar-check"></i></div>
                    </x-slot>
                    <x-slot name="bottomSlot">
                        <span class="text-muted small">
                            The homeowner may claim the document at the HOA office from this moment.
                        </span>
                    </x-slot>
                </x-adminlte-input>

                <x-adminlte-textarea name="remarks" label="Remarks (optional)" rows="3"
                    placeholder="Anything worth keeping on record — what was issued, when it was released, who attended to it."
                    wire:model.defer="remarks" igroup-size="sm" />
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Approve" theme="success" icon="fas fa-fw fa-check"
                wire:click="approve" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="approve" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
