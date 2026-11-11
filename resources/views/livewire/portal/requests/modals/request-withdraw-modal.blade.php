<div wire:ignore.self>
    <x-adminlte-modal id="requestWithdrawModal"
        title="Delete Request" icon="fas fa-fw fa-trash" theme="danger" size="md"
        scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold text-break">{{ $request->type_label }}</div>
                    <div class="text-muted small">{{ $request->reference ?: '—' }}</div>
                </div>

                <p class="mb-0">
                    Deleting removes this filing from your requests. Do this only while the office
                    has not acted on it yet — an approved request cannot be deleted.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Keep Request" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Request" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="withdraw" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="withdraw" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
