<div wire:ignore.self>
    <x-adminlte-modal id="paymentMethodFormModal"
        title="{{ $methodId ? 'Edit Payment Method' : 'Add Payment Method' }}"
        icon="fas fa-fw fa-credit-card" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the payment method…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <x-adminlte-input name="form.name" label="Payment Method"
                placeholder="e.g., GCash, Bank Transfer, Cash"
                wire:model.defer="form.name" igroup-size="sm" />

            <x-adminlte-textarea name="form.details" label="Details / Instructions" rows="2"
                placeholder="e.g., Scan QR or manually type 0945 351 3307"
                wire:model.defer="form.details" igroup-size="sm" />

            {{-- QR code --}}
            <label class="mb-1">QR Code</label>
            <div class="d-flex align-items-start mb-3" style="gap:1rem;">
                <div class="border rounded p-2 text-center" style="min-width:110px;">
                    @if ($qr)
                        <img src="{{ $qr->temporaryUrl() }}" alt="QR preview"
                            style="max-height:90px;max-width:90px;">
                    @elseif ($qr_path && ! $removeQr)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($qr_path) }}"
                            alt="QR code" style="max-height:90px;max-width:90px;">
                    @else
                        <span class="text-muted small d-inline-block py-4">No QR code</span>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <x-adminlte-input-file name="qr" label="Upload QR code"
                        placeholder="PNG or JPG, up to 2MB"
                        wire:model="qr" igroup-size="sm" legend="Browse">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-qrcode"></i></div>
                        </x-slot>
                        @if ($qr_path && ! $removeQr)
                            <x-slot name="prependSlot">
                                <button type="button" class="btn btn-sm btn-primary"
                                    wire:click="removeQr" wire:loading.attr="disabled">
                                    <i class="fas fa-fw fa-trash-alt"></i>
                                </button>
                            </x-slot>
                        @endif
                    </x-adminlte-input-file>

                    <div wire:loading wire:target="qr" class="mt-1">
                        <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                    </div>
                </div>
            </div>

            <div class="custom-control custom-switch mt-2">
                <input type="checkbox" class="custom-control-input" id="methodIsActive"
                    wire:model="form.is_active">
                <label class="custom-control-label" for="methodIsActive">Offer this method</label>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $methodId ? 'Save' : 'Add' }}" theme="primary"
                icon="fas fa-fw fa-save" wire:click="confirm" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
