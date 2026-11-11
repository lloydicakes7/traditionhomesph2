<div wire:ignore.self>
    <x-adminlte-modal id="paymentApproveModal" title="Approve Payment"
        icon="fas fa-fw fa-check-circle" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading payment…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($payment)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $payment->amount_display }} for {{ $payment->bill_reference }}</div>
                    <div class="text-muted small">
                        {{ $payment->bill_type_label }} &middot; {{ $payment->paid_at_display }}
                    </div>
                    <div class="small mt-1">
                        Filed by <span class="font-weight-medium">{{ $payment->bill?->issued_to_name }}</span>
                        &middot; {{ $payment->method_label }}
                        @if ($payment->reference_no)
                            &middot; Ref. {{ $payment->reference_no }}
                        @endif
                    </div>
                    @if ($payment->notes)
                        <div class="text-muted small mt-1">{{ $payment->notes }}</div>
                    @endif
                    @if ($payment->proof_url)
                        <div class="mt-2">
                            <a href="{{ $payment->proof_url }}" target="_blank" class="small">
                                <i class="fas fa-fw fa-image mr-1"></i> View proof of payment
                            </a>
                        </div>
                    @endif
                </div>

                @error('approval')
                    <div class="alert alert-danger py-2 small">{{ $message }}</div>
                @enderror

                <p class="mb-0">
                    Record this payment against the bill?
                    <span class="d-block text-muted small mt-1">
                        The bill recomputes from it — its balance drops by
                        {{ $payment->amount_display }}, and it settles when nothing is left.
                    </span>
                </p>
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
