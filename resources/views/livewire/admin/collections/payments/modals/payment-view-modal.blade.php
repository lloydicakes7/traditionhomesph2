<div wire:ignore.self>
    <x-adminlte-modal id="paymentViewModal" title="Payment Details"
        icon="fas fa-fw fa-receipt" theme="info" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading payment…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($payment)
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="h5 mb-0 font-weight-bold">{{ $payment->amount_display }}</div>
                    <div class="text-muted small">{{ $payment->paid_at_display }}</div>
                </div>
                <span class="badge badge-success">{{ $payment->method_label }}</span>
            </div>

            <dl class="row mb-0 small">
                <dt class="col-5 text-muted font-weight-normal">Bill</dt>
                <dd class="col-7 font-weight-medium">
                    {{ $payment->bill_reference }}
                    <span class="text-muted">({{ $payment->bill_type_label }})</span>
                </dd>

                <dt class="col-5 text-muted font-weight-normal">Paid By</dt>
                <dd class="col-7">{{ $payment->user?->email }}</dd>

                <dt class="col-5 text-muted font-weight-normal">Reference No.</dt>
                <dd class="col-7">{{ $payment->reference_no ?: '—' }}</dd>

                <dt class="col-5 text-muted font-weight-normal">Received By</dt>
                <dd class="col-7">{{ $payment->receivedByAdmin?->name ?: 'System' }}</dd>

                @if ($payment->advanceDeposit)
                    <dt class="col-5 text-muted font-weight-normal">Drawn From</dt>
                    <dd class="col-7">
                        Advance deposit #{{ $payment->advanceDeposit->id }}
                        ({{ $payment->advanceDeposit->property_label }})
                    </dd>
                @endif

                @if ($payment->proof_path)
                    <dt class="col-5 text-muted font-weight-normal">Proof of Payment</dt>
                    <dd class="col-7">
                        <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener">
                            <img src="{{ $payment->proof_url }}" alt="Proof of payment"
                                class="rounded border elevation-1" style="max-height:140px;">
                        </a>
                    </dd>
                @endif

                @if ($payment->notes)
                    <dt class="col-5 text-muted font-weight-normal">Notes</dt>
                    <dd class="col-7">{{ $payment->notes }}</dd>
                @endif
            </dl>
            @endif
        </div>

        <x-slot name="footerSlot">
            @if ($payment && $payment->is_recorded && $payment->receipt_no)
                @can('view', \App\Models\Payment::class)
                    <a class="btn btn-outline-secondary btn-sm"
                        href="{{ route('collections.payments.receipt.show', $payment) }}"
                        target="_blank" rel="noopener"
                        aria-label="{{ __('payments.receipt.print_title') }}"
                        title="{{ __('payments.receipt.print_title') }}">
                        <i class="fas fa-fw fa-print"></i> {{ __('payments.receipt.print') }}
                    </a>
                @endcan
            @endif
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>

</div>
