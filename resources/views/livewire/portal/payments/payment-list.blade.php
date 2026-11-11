<div>
    {{-- =================================================================== --}}
    {{-- HEADER                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12">
                <h5 class="mb-0 font-weight-bold">My Payments</h5>
                <small class="text-muted">
                    Every payment the association's office has recorded against your bills. Payments are
                    received in person at the HOA office.
                </small>
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
                            <th style="width:16%;">Date</th>
                            <th style="width:18%;">Bill</th>
                            <th class="text-right" style="width:13%;">Amount</th>
                            <th style="width:20%;">Method</th>
                            <th style="width:17%;">Reference</th>
                            <th class="text-center" style="width:16%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr wire:key="my-payment-{{ $payment->id }}">
                                <td class="align-middle text-truncate" style="max-width:0;">
                                    {{ $payment->paid_at_display }}
                                </td>
                                <td class="align-middle text-truncate" style="max-width:0;">
                                    <div class="font-weight-medium text-truncate">{{ $payment->bill_reference }}</div>
                                    <div class="text-muted small text-truncate">{{ $payment->bill_type_label }}</div>
                                </td>
                                <td class="align-middle text-right font-weight-medium">{{ $payment->amount_display }}</td>
                                <td class="align-middle">{{ $payment->method_label }}</td>
                                <td class="align-middle text-truncate" style="max-width:0;">
                                    {{ $payment->reference_no ?: '—' }}
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-{{ $payment->status_badge }}"
                                        title="{{ $payment->status_enum?->description() }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                                    <p class="mb-0">No payments have been recorded for your account yet.</p>
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
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($payments as $payment)
            <div class="card shadow-sm mb-3" wire:key="my-payment-mobile-{{ $payment->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate">{{ $payment->bill_reference }}</div>
                            <small class="text-muted text-truncate d-block">{{ $payment->bill_type_label }}</small>
                        </div>
                        <span class="badge badge-{{ $payment->status_badge }} flex-shrink-0"
                            title="{{ $payment->status_enum?->description() }}">
                            {{ $payment->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2" style="gap:0.75rem;">
                        <div class="text-muted small">Amount</div>
                        <div class="font-weight-bold">{{ $payment->amount_display }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-1" style="gap:0.75rem;">
                        <div class="text-muted small">Method</div>
                        <div class="small text-right">{{ $payment->method_label }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-1" style="gap:0.75rem;">
                        <div class="text-muted small">Reference</div>
                        <div class="small text-right text-break">{{ $payment->reference_no ?: '—' }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-start" style="gap:0.75rem;">
                        <div class="text-muted small">Date</div>
                        <div class="small text-right">{{ $payment->paid_at_display }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                    <p class="mb-0">No payments have been recorded for your account yet.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
