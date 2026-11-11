<div>
    {{-- =================================================================== --}}
    {{-- WHOSE ACCOUNT, AND FOR WHICH PERIOD                                  --}}
    {{-- =================================================================== --}}
    <x-reports.toolbar subtitle="One homeowner's account for one period.">
        <x-slot name="filters">
            <div class="report-filter-field">
                <x-adminlte-select2 id="statementUserId" name="userId" wire:model="userId"
                    igroup-size="sm" fgroup-class="mb-0"
                    :config="['placeholder' => empty($homeowners) ? '— No balance due —' : '— Select a homeowner —']">
                    <option value="">{{ empty($homeowners) ? '— No balance due —' : '— Select a homeowner —' }}</option>
                    @foreach ($homeowners as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select2>
            </div>

            <div class="report-filter-field">
                <x-adminlte-select name="period" wire:model="period" igroup-size="sm" fgroup-class="mb-0">
                    @foreach ($periods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <span wire:loading wire:target="userId,period" class="text-info small">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </x-slot>
    </x-reports.toolbar>

    @if (! $statement)
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-fw fa-file-invoice fa-2x mb-2"></i>
                <p class="mb-0">{{ $emptyMessage }}</p>
            </div>
        </div>
    @else
        {{-- ============================================================== --}}
        {{-- THE STATEMENT ITSELF                                            --}}
        {{-- ============================================================== --}}
        <div class="card">
            <div class="card-header">
                <span class="font-weight-bold"><i class="fas fa-fw fa-file-invoice mr-1"></i> Statement of Account</span>
                <span class="float-right text-muted small">{{ $statement['period'] }}</span>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <div class="text-muted small text-uppercase">Billed To</div>
                        <div class="font-weight-bold">{{ $statement['owner'] }}</div>
                        <div class="text-muted small">{{ $statement['address'] }}</div>
                    </div>
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <div class="text-muted small text-uppercase">Property</div>
                        <div class="font-weight-bold">{{ $statement['property'] }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small text-uppercase">Balance Due</div>
                        <div class="h4 mb-0 font-weight-bold text-danger">{{ $statement['balance_display'] }}</div>
                    </div>
                </div>

                {{-- What the period billed: dues and other charges, one running
                     account — a table on a wide screen, a card per charge on a
                     small one --}}
                @php
                    $cards = collect($statement['bills'])->map(function ($bill) {
                        return [
                            'title'    => $bill->reference,
                            'subtitle' => $bill->type_label,
                            'facts'    => [
                                ['label' => 'Particulars', 'value' => $bill->items->pluck('name')->implode(', ') ?: 'Charge'],
                                ['label' => 'Period / Date', 'value' => $bill->billing_period_display !== '—' ? $bill->billing_period_display : $bill->bill_date_display],
                                ['label' => 'Amount', 'value' => \App\Utility\MoneyHelper::format($bill->total)],
                            ],
                            'total' => ['label' => 'Balance', 'value' => \App\Utility\MoneyHelper::format($bill->balance)],
                        ];
                    })->values()->all();

                    $cards[] = [
                        'title' => 'Total Balance Due',
                        'total' => ['label' => 'Balance due', 'value' => $statement['balance_display']],
                    ];
                @endphp

                <h6 class="text-uppercase text-muted">Charges</h6>
                <div class="report-table-view">
                    <table class="table table-report mb-0" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 16%;">Reference</th>
                                <th>Type</th>
                                <th>Particulars</th>
                                <th style="width: 14%;">Period / Date</th>
                                <th class="text-right" style="width: 14%;">Amount</th>
                                <th class="text-right" style="width: 14%;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statement['bills'] as $bill)
                                <tr>
                                    <td class="align-middle">{{ $bill->reference }}</td>
                                    <td class="align-middle">{{ $bill->type_label }}</td>
                                    <td class="align-middle text-break">{{ $bill->items->pluck('name')->implode(', ') ?: 'Charge' }}</td>
                                    <td class="align-middle">{{ $bill->billing_period_display !== '—' ? $bill->billing_period_display : $bill->bill_date_display }}</td>
                                    <td class="text-right amount-mono align-middle">{{ \App\Utility\MoneyHelper::format($bill->total) }}</td>
                                    <td class="text-right amount-mono align-middle">{{ \App\Utility\MoneyHelper::format($bill->balance) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Nothing is outstanding for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right">TOTAL BALANCE DUE</td>
                                <td class="text-right amount-mono">{{ $statement['balance_display'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <x-reports.cards class="mt-2" :rows="$cards"
                    empty="Nothing is outstanding for this period." />
            </div>
        </div>
    @endif
</div>
