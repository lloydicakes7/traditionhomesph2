@if ($payment->is_recorded && $payment->receipt_no)
    @can('view', \App\Models\Payment::class)
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('collections.payments.receipt.show', $payment) }}"
            data-receipt-print="{{ route('collections.payments.receipt.show', $payment) }}">
            <i class="fas fa-fw fa-print mr-1"></i> {{ __('payments.receipt.print_a4') }}
        </a>
        <a class="dropdown-item" href="{{ route('collections.payments.receipt.thermal.show', $payment) }}"
            data-receipt-print="{{ route('collections.payments.receipt.thermal.show', $payment) }}">
            <i class="fas fa-fw fa-receipt mr-1"></i> {{ __('payments.receipt.print_thermal') }}
        </a>
        <a class="dropdown-item" href="{{ route('collections.payments.receipt.download', $payment) }}">
            <i class="fas fa-fw fa-file-pdf mr-1"></i> {{ __('payments.receipt.download_a4') }}
        </a>
        <a class="dropdown-item" href="{{ route('collections.payments.receipt.thermal.download', $payment) }}">
            <i class="fas fa-fw fa-file-alt mr-1"></i> {{ __('payments.receipt.download_thermal') }}
        </a>
    @endcan
@endif
