<div wire:ignore.self>
    <x-adminlte-modal id="advanceDepositViewModal" title="Advance Deposit Details"
        icon="fas fa-fw fa-piggy-bank" theme="info" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading advance deposit…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($deposit)
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="h5 mb-0 font-weight-bold">{{ $deposit->balance_display }}</div>
                        <div class="text-muted small">credit still held</div>
                    </div>
                    <span class="badge badge-{{ $deposit->status_enum?->badgeTheme() ?? 'secondary' }}">
                        {{ $deposit->status_enum?->label() ?? '—' }}
                    </span>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted font-weight-normal">Homeowner</dt>
                    <dd class="col-7 font-weight-medium">{{ $deposit->holder_name }}</dd>

                    <dt class="col-5 text-muted font-weight-normal">Amount Received</dt>
                    <dd class="col-7">{{ $deposit->amount_display }}</dd>

                    <dt class="col-5 text-muted font-weight-normal">Date Received</dt>
                    <dd class="col-7">{{ $deposit->deposited_at_display }}</dd>

                    @if ($deposit->notes)
                        <dt class="col-5 text-muted font-weight-normal">Notes</dt>
                        <dd class="col-7">{{ $deposit->notes }}</dd>
                    @endif
                </dl>

                <label class="mb-1 mt-3">Bills Settled From This Credit</label>
                <div class="table-responsive border rounded">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Bill</th>
                                <th style="width:110px;" class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deposit->payments as $payment)
                                <tr>
                                    <td class="small">{{ $payment->paid_at_display }}</td>
                                    <td class="small">
                                        {{ $payment->bill?->reference ?? '—' }}
                                    </td>
                                    <td class="text-right">{{ $payment->amount_display }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Nothing has been settled from this credit yet.
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
