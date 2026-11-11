<div>
    {{-- =================================================================== --}}
    {{-- YEAR + EXPORT                                                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card class="mb-3">
        <div class="row align-items-center">
            <div class="col-12 col-sm-4 mb-1 mb-sm-0">
                <x-adminlte-select name="year" wire:model="year" igroup-size="sm" fgroup-class="mb-1">
                    @foreach ($years as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
                <p class="text-muted small mb-0">
                    Collected <span class="font-weight-bold">{{ $matrix['year_collected'] }}</span>
                    · outstanding <span class="font-weight-bold text-danger">{{ $matrix['year_balance'] }}</span>
                </p>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                @can('exportMonthlyDues', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-export"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP (xl and up)                                                 --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-xl-block">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0 w-100 small">
                <thead class="bg-light">
                    <tr>
                        <th class="px-1">Property</th>
                        @foreach ($matrix['months'] as $month)
                            <th class="px-1">{{ $month['label'] }}</th>
                        @endforeach
                        <th class="px-1">Collected</th>
                        <th class="px-1">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($matrix['rows'] as $row)
                        <tr>
                            <td class="px-1 text-break">
                                <div class="font-weight-medium small">{{ $row['property']->block_lot_label }}</div>
                                <div class="text-muted small">{{ $row['property']->owner?->name ?: 'No household on file' }}</div>
                            </td>
                            @foreach ($matrix['months'] as $month)
                                @php
                                    $due = $row['cells'][$month['value']] ?? null;
                                    $paid = $due ? (float) $due->paid_amount : 0.0;
                                    $balance = $due ? (float) $due->balance : 0.0;
                                @endphp
                                <td class="px-1">
                                    @if ($due && $paid <= 0 && $balance > 0)
                                        <div class="text-danger small" title="{{ $due->balance_display }}">{{ number_format($balance, 0) }}</div>
                                    @elseif ($due && $balance > 0)
                                        <div class="text-muted small" title="{{ $due->total_display }}">{{ number_format((float) $due->total, 0) }}</div>
                                        <div class="text-danger small" title="{{ $due->balance_display }}">{{ number_format($balance, 0) }}</div>
                                    @elseif ($due)
                                        <div class="text-success small" title="{{ $due->total_display }}">{{ number_format((float) $due->total, 0) }}</div>
                                    @else
                                        <div class="text-muted small">0</div>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-1 font-weight-medium small">{{ $row['row_collected'] }}</td>
                            <td class="px-1 small {{ $row['year_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $row['row_balance'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($matrix['months']) + 3 }}" class="text-center text-muted py-4">
                                No properties on file yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <th class="px-1 py-3 small">Monthly Collected</th>
                        @foreach ($matrix['months'] as $month)
                            <th class="px-1 py-3 small">{{ $matrix['collected_display'][$month['value']] ?? '—' }}</th>
                        @endforeach
                        <th class="px-1 py-3 small">{{ $matrix['year_collected'] }}</th>
                        <th class="px-1 py-3 small">{{ $matrix['year_balance'] }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE / TABLET (below xl)                                          --}}
    {{-- =================================================================== --}}
    <div class="d-block d-xl-none">
        {{-- Monthly totals summary --}}
        <x-adminlte-card class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-weight-bold small text-uppercase text-muted">Monthly Collected</span>
                <span class="small font-weight-bold">{{ $matrix['year_collected'] }} total</span>
            </div>
            <div class="d-flex flex-wrap" style="gap:0.4rem;">
                @foreach ($matrix['months'] as $month)
                    <div class="border rounded px-2 py-1 text-center" style="min-width:64px;">
                        <div class="text-muted" style="font-size:0.65rem;">{{ $month['label'] }}</div>
                        <div class="small font-weight-medium">
                            {{ $matrix['collected_display'][$month['value']] ?? '—' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-adminlte-card>

        {{-- One card per property --}}
        @forelse ($matrix['rows'] as $row)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                        {{ $row['property']->block_lot_label }}
                    </div>
                    <div class="text-muted small text-truncate">
                        {{ $row['property']->owner?->name ?: 'No household on file' }}
                    </div>
                </div>

                <div class="card-body px-3 py-3">
                    <div class="d-flex flex-wrap" style="gap:0.4rem;">
                        @foreach ($matrix['months'] as $month)
                            @php
                                $due = $row['cells'][$month['value']] ?? null;
                                $paid = $due ? (float) $due->paid_amount : 0.0;
                                $balance = $due ? (float) $due->balance : 0.0;
                            @endphp
                            <div class="border rounded px-2 py-1 text-center" style="min-width:64px;">
                                <div class="text-muted" style="font-size:0.65rem;">{{ $month['label'] }}</div>
                                @if ($due && $paid <= 0 && $balance > 0)
                                    <div class="text-danger small">{{ $due->balance_display }}</div>
                                @elseif ($due && $balance > 0)
                                    <div class="text-muted small" style="font-size:0.7rem;">{{ $due->total_display }}</div>
                                    <div class="text-danger small">{{ $due->balance_display }}</div>
                                @elseif ($due)
                                    <div class="text-success small">{{ $due->total_display }}</div>
                                @else
                                    <div class="text-muted small">0</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer bg-light py-2">
                    <div class="d-flex justify-content-between" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted" style="font-size:0.7rem;">COLLECTED</div>
                            <div class="small font-weight-medium">{{ $row['row_collected'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted" style="font-size:0.7rem;">BALANCE</div>
                            <div class="small font-weight-medium {{ $row['year_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $row['row_balance'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    No properties on file yet.
                </div>
            </div>
        @endforelse
    </div>

    {{-- =================================================================== --}}
    {{-- PAGINATION                                                          --}}
    {{-- =================================================================== --}}
    @if ($matrix['rows']->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $matrix['rows']->links() }}
        </div>
    @endif
</div>
