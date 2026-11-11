<div wire:ignore.self>
    <x-adminlte-modal id="soaViewModal" title="Statement Preview"
        icon="fas fa-fw fa-file-alt" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading statement…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($statement)
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="h5 mb-0 font-weight-bold">{{ $summary['owner'] }}</div>
                    <div class="text-muted small">
                        {{ $summary['property'] }} · {{ $summary['address'] }}
                    </div>
                </div>
                <span class="badge badge-{{ $statement->status_enum?->badgeTheme() ?? 'secondary' }}">
                    {{ $statement->status_enum?->label() ?? '—' }}
                </span>
            </div>

            <p class="text-muted small">
                Statement for <strong>{{ $summary['period'] }}</strong>, to be mailed to
                <strong>{{ $statement->email }}</strong>.
            </p>

            <label class="mb-1">Outstanding Bills</label>
            <div class="table-responsive border rounded mb-3">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Period / Date</th>
                            <th class="text-right" style="width:110px;">Amount</th>
                            <th class="text-right" style="width:110px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summary['bills'] as $bill)
                            <tr>
                                <td class="small">{{ $bill->reference }}</td>
                                <td class="small">{{ $bill->type_label }}</td>
                                <td class="small">
                                    {{ $bill->billing_period_display !== '—' ? $bill->billing_period_display : $bill->bill_date_display }}
                                </td>
                                <td class="text-right">{{ $bill->total_display }}</td>
                                <td class="text-right">{{ $bill->balance_display }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Nothing is outstanding for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-weight-bold">Total outstanding</td>
                            <td class="text-right font-weight-bold">{{ $summary['balance_display'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($summary['instructions'])
                <label class="mb-1">Instructions</label>
                {{-- The instructions are rich text, sanitized on the way into the
                     settings (see App\Utility\HtmlHelper). --}}
                <div class="border rounded p-3 bg-light small">{!! $summary['instructions'] !!}</div>
            @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>

</div>
