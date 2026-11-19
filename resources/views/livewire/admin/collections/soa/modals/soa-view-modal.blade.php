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
                    {{ $statement->status_enum?->label() ?? 'Status not recorded' }}
                </span>
            </div>

            <p class="text-muted small">
                Statement for <strong>{{ $summary['period'] }}</strong>, to be mailed to
                <strong>{{ $statement->email }}</strong>.
            </p>

            @php $hasBills = collect($summary['groups'])->sum(fn ($group) => $group['bills']->count()); @endphp

            @foreach ($summary['groups'] as $group)
                @if ($group['bills']->isNotEmpty())
                    <label class="mb-1">{{ $group['label'] }}</label>
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
                                @foreach ($group['bills'] as $bill)
                                    <tr>
                                        <td class="small">{{ $bill->reference }}</td>
                                        <td class="small">{{ $bill->type_label }}</td>
                                        <td class="small">
                                            {{ $bill->billing_period ? $bill->billing_period_display : $bill->bill_date_display }}
                                        </td>
                                        <td class="text-right">{{ $bill->total_display }}</td>
                                        <td class="text-right">{{ $bill->balance_display }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right font-weight-bold">{{ $group['label'] }} total</td>
                                    <td class="text-right font-weight-bold">{{ $group['total_display'] }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            @endforeach

            @if (! $hasBills)
                <div class="border rounded p-3 mb-3 text-center text-muted small">
                    Nothing is outstanding for this period.
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-3">
                <span class="font-weight-bold">Total outstanding</span>
                <span class="font-weight-bold">{{ $summary['balance_display'] }}</span>
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
