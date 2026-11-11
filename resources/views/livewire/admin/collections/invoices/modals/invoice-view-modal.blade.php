<div wire:ignore.self>
    <x-adminlte-modal id="invoiceViewModal" title="Invoice Details"
        icon="fas fa-fw fa-file-invoice-dollar" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading invoice…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($invoice)
            {{-- ========================================================= --}}
            {{-- HEADER: reference, status, who it is issued to              --}}
            {{-- ========================================================= --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="h5 mb-0 font-weight-bold">{{ $invoice->reference }}</div>
                    <div class="text-muted small">
                        Raised {{ $invoice->bill_date_display }}
                        @if ($invoice->due_date)
                            · due {{ $invoice->due_date->format('M j, Y') }}
                        @endif
                    </div>
                </div>
                <span class="badge badge-{{ $invoice->status_badge }}">
                    {{ $invoice->status_label }}
                </span>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted font-weight-normal">Issued To</dt>
                        <dd class="col-7 font-weight-medium">{{ $invoice->issued_to_name }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Property</dt>
                        <dd class="col-7 font-weight-medium">{{ $invoice->property?->block_lot_label ?: '—' }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Address</dt>
                        <dd class="col-7">{{ $invoice->property?->address?->short_address ?: '—' }}</dd>
                    </dl>
                </div>
                <div class="col-12 col-sm-6">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted font-weight-normal">Raised By</dt>
                        <dd class="col-7">{{ $invoice->createdByAdmin?->name ?: 'System' }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Billed Amount</dt>
                        <dd class="col-7 font-weight-bold">{{ $invoice->total_display }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Balance</dt>
                        <dd class="col-7 font-weight-bold {{ (float) $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $invoice->balance_display }}
                        </dd>
                    </dl>
                </div>
            </div>

            @if ($invoice->notes)
                <p class="small text-muted mb-3">{{ $invoice->notes }}</p>
            @endif

            {{-- ========================================================= --}}
            {{-- LINE ITEMS                                                  --}}
            {{-- ========================================================= --}}
            <label class="mb-1">Items</label>
            <div class="table-responsive border rounded mb-3">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th style="width:80px;" class="text-right">Qty</th>
                            <th style="width:120px;" class="text-right">Unit Price</th>
                            <th style="width:120px;" class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td>
                                    <div class="font-weight-medium">{{ $item->name }}</div>
                                    @if ($item->description)
                                        <div class="text-muted small">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="text-right">{{ $item->quantity_display }}</td>
                                <td class="text-right">{{ $item->unit_price_display }}</td>
                                <td class="text-right">{{ $item->amount_display }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No items on this invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right text-muted">Subtotal</td>
                            <td class="text-right font-weight-medium">
                                {{ \App\Utility\MoneyHelper::format($invoice->subtotal) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right text-muted">Discount</td>
                            <td class="text-right text-danger">-{{ $invoice->discount_display }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right text-muted">Penalty</td>
                            <td class="text-right">{{ $invoice->penalty_display }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold">Total</td>
                            <td class="text-right font-weight-bold">{{ $invoice->total_display }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right text-muted">Paid</td>
                            <td class="text-right text-success">{{ $invoice->paid_display }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right font-weight-bold">Balance</td>
                            <td class="text-right font-weight-bold">{{ $invoice->balance_display }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ========================================================= --}}
            {{-- PAYMENT HISTORY                                             --}}
            {{-- ========================================================= --}}
            <label class="mb-1">Payment History</label>
            <div class="table-responsive border rounded">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th style="width:120px;" class="text-right">Amount</th>
                            <th style="width:60px;" class="text-center">{{ __('payments.receipt.column') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->payments as $payment)
                            <tr>
                                <td class="small">{{ $payment->paid_at_display }}</td>
                                <td class="small">{{ $payment->reference_no ?: '—' }}</td>
                                <td class="small">{{ $payment->method_label }}</td>
                                <td class="text-right">{{ $payment->amount_display }}</td>
                                <td class="text-center">
                                    @if ($payment->is_recorded && $payment->receipt_no)
                                        @can('view', \App\Models\Payment::class)
                                            <a class="btn btn-outline-secondary btn-sm"
                                                href="{{ route('collections.payments.receipt.show', $payment) }}"
                                                target="_blank" rel="noopener"
                                                aria-label="{{ __('payments.receipt.print_title') }}"
                                                title="{{ __('payments.receipt.print_title') }}">
                                                <i class="fas fa-fw fa-print"></i>
                                            </a>
                                        @endcan
                                    @else
                                        <span class="text-muted small">{{ __('payments.receipt.not_recorded') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No payments recorded against this invoice yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>

</div>
