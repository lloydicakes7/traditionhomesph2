<div>
    {{-- =================================================================== --}}
    {{-- HEADER — what the account still owes                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-8 mb-2 mb-sm-0">
                <h5 class="mb-0 font-weight-bold">Statement of Account</h5>
                <small class="text-muted">
                    The bills still open on your account. Settle them in person at the HOA office, where the
                    officer records your payment.
                </small>
            </div>

            <div class="col-12 col-sm-4 text-sm-right">
                <div class="text-muted small">Outstanding Balance</div>
                <div class="font-weight-bold" style="font-size:1.35rem;">{{ \App\Utility\MoneyHelper::format($totalBalance) }}</div>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                    <thead>
                        <tr>
                            <th style="width:15%;">Bill</th>
                            <th style="width:15%;">Type</th>
                            <th style="width:15%;">Due Date</th>
                            <th class="text-right" style="width:10%;">Amount</th>
                            <th class="text-right" style="width:10%;">Paid</th>
                            <th class="text-right" style="width:10%;">Balance</th>
                            <th class="text-center" style="width:20%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bills as $bill)
                            <tr wire:key="statement-bill-{{ $bill->id }}">
                                <td class="align-middle text-truncate" style="max-width:0;">
                                    <div class="font-weight-medium text-truncate">{{ $bill->reference }}</div>
                                    @if ($bill->billing_period)
                                        <div class="text-muted small text-truncate">{{ $bill->billing_period_display }}</div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $bill->type_label }}</td>
                                <td class="align-middle">{{ $bill->due_date ? $bill->due_date->format('M j, Y') : '—' }}</td>
                                <td class="align-middle text-right">{{ $bill->total_display }}</td>
                                <td class="align-middle text-right">{{ $bill->paid_display }}</td>
                                <td class="align-middle text-right font-weight-medium">{{ $bill->balance_display }}</td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-{{ $bill->status_badge }}">{{ $bill->status_label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-fw fa-check-circle fa-2x mb-2"></i>
                                    <p class="mb-0">Nothing is owed — your account has no open bills.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE / TABLET CARDS                                               --}}
    {{-- Same record as the desktop row, laid out as a stacked card so nothing --}}
    {{-- is squeezed on a narrow screen.                                      --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($bills as $bill)
            <div class="card shadow-sm mb-3" wire:key="statement-mobile-bill-{{ $bill->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate">{{ $bill->reference }}</div>
                            <small class="text-muted text-truncate d-block">
                                {{ $bill->type_label }}
                                @if ($bill->billing_period)
                                    &middot; {{ $bill->billing_period_display }}
                                @endif
                            </small>
                        </div>
                        <span class="badge badge-{{ $bill->status_badge }} flex-shrink-0">{{ $bill->status_label }}</span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                            <div class="font-weight-medium">{{ $bill->total_display }}</div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="text-muted small" style="font-size:0.7rem;">BALANCE</div>
                            <div class="font-weight-medium">{{ $bill->balance_display }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:0.7rem;">PAID</div>
                            <div>{{ $bill->paid_display }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:0.7rem;">DUE DATE</div>
                            <div>{{ $bill->due_date ? $bill->due_date->format('M j, Y') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-check-circle fa-2x mb-2"></i>
                    <p class="mb-0">Nothing is owed — your account has no open bills.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
