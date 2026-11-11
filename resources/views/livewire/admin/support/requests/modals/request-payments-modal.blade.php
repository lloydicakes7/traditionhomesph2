<div wire:ignore.self>
    <x-adminlte-modal id="requestPaymentsModal" title="Request Payments"
        icon="fas fa-fw fa-file-invoice-dollar" theme="success" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading payments…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request && $request->bill)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start" style="gap:0.75rem;">
                        <div>
                            <div class="font-weight-bold">{{ $request->bill->reference }}</div>
                            <div class="text-muted small">
                                {{ $request->bill->type_label }} &middot; {{ $request->bill->issued_to_name }}
                            </div>
                        </div>
                        <span class="badge badge-{{ $request->bill->status_badge }}">
                            {{ $request->bill->status_label }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small">
                        <span class="text-muted">Total</span>
                        <span class="font-weight-medium">{{ $request->bill->total_display }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Paid</span>
                        <span class="font-weight-medium">{{ $request->bill->paid_display }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Balance</span>
                        <span class="font-weight-medium">{{ $request->bill->balance_display }}</span>
                    </div>
                </div>

                {{-- The receipts filed against the invoice. --}}
                @if ($payments->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-fw fa-file-invoice-dollar fa-2x mb-2"></i>
                        <p class="mb-0">No payments have been filed for this invoice yet.</p>
                    </div>
                @else
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Paid</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr wire:key="request-payment-{{ $payment->id }}">
                                    <td>
                                        {{ $payment->paid_at_display }}
                                        @if ($payment->reference_no)
                                            <div class="text-muted small">Ref. {{ $payment->reference_no }}</div>
                                        @endif
                                    </td>
                                    <td class="font-weight-medium">{{ $payment->amount_display }}</td>
                                    <td>{{ $payment->method_label }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $payment->status_badge }}">
                                            {{ $payment->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-right align-middle">
                                        @if ($payment->is_pending)
                                            @can('approve', \App\Models\Payment::class)
                                                <x-adminlte-button label="Approve" theme="success" icon="fas fa-fw fa-check"
                                                    wire:click="approve({{ $payment->id }})" wire:loading.attr="disabled"
                                                    class="btn-xs" />
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
