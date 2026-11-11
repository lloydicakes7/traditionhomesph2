<div wire:ignore.self>
    <x-adminlte-modal id="dueViewModal" title="Monthly Due Details"
        icon="fas fa-fw fa-calendar-alt" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading dues…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($due)
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="h5 mb-0 font-weight-bold">{{ $due->reference }}</div>
                    <div class="text-muted small">
                        {{ $due->billing_period_display }} · billed {{ $due->bill_date_display }}
                    </div>
                </div>
                <span class="badge badge-{{ $due->status_badge }}">{{ $due->status_label }}</span>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted font-weight-normal">Billed To</dt>
                        <dd class="col-7 font-weight-medium">{{ $due->issued_to_name }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Property</dt>
                        <dd class="col-7 font-weight-medium">{{ $due->property->block_lot_label }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Address</dt>
                        <dd class="col-7">{{ $due->property?->address?->short_address ?: '—' }}</dd>
                    </dl>
                </div>
                <div class="col-12 col-sm-6">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted font-weight-normal">Bill Date</dt>
                        <dd class="col-7">{{ $due->bill_date_display }}</dd>

                        <dt class="col-5 text-muted font-weight-normal">Due Date</dt>
                        <dd class="col-7">{{ $due->due_date ? $due->due_date->format('M j, Y') : '—' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- How the month was priced: the rate and the area it was charged
                 against are the bill's own line, snapshotted when it was raised. --}}
            <label class="mb-1">Charges</label>
            <div class="table-responsive border rounded mt-3">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-right" style="width:90px;">Qty</th>
                            <th class="text-right" style="width:120px;">Unit Price</th>
                            <th class="text-right" style="width:120px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($due->items as $item)
                            <tr>
                                <td class="small">{{ $item->name }}</td>
                                <td class="text-right small">{{ $item->quantity_display }}</td>
                                <td class="text-right small">{{ $item->unit_price_display }}</td>
                                <td class="text-right small">{{ $item->amount_display }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive border rounded mt-3 mb-3">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Amount</td>
                            <td class="text-right">{{ $due->subtotal_display }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Discount</td>
                            <td class="text-right text-danger">-{{ $due->discount_display }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Penalty</td>
                            <td class="text-right">{{ $due->penalty_display }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total</td>
                            <td class="text-right font-weight-bold">{{ $due->total_display }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Paid</td>
                            <td class="text-right text-success">{{ $due->paid_display }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Balance</td>
                            <td class="text-right font-weight-bold {{ (float) $due->balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $due->balance_display }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <label class="mb-1">Payment History</label>
            <div class="table-responsive border rounded">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th style="width:110px;" class="text-right">Amount</th>
                            <th style="width:60px;" class="text-center">{{ __('payments.receipt.column') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($due->payments as $payment)
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
                                    No payments recorded against these dues yet.
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
