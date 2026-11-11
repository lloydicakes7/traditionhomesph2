@extends('layouts.admin')

@section('title', 'Payments')

@section('breadcrumb')
    <li class="breadcrumb-item">Collections</li>
@endsection

@section('page-content')
    <div>
        @livewire('admin.collections.payments.payment-table-list')

        {{-- Per-page modals (event-driven siblings) --}}
        @livewire('admin.collections.payments.modals.payment-view-modal')
        @livewire('admin.collections.payments.modals.payment-approve-modal')
        @livewire('admin.collections.payments.modals.payment-delete-modal')
        @livewire('admin.collections.payments.modals.payment-mass-delete-modal')
        @livewire('admin.collections.modals.record-payment-modal')
        @livewire('admin.accounting.journal-entries.modals.post-to-journal-modal')
    </div>
@endsection

@push('js')
    <script>
        // Print a receipt without leaving the page: the PDF loads into a hidden
        // iframe and the iframe's own viewer prints it.
        $(document).on('click', '[data-receipt-print]', function (event) {
            event.preventDefault();

            var url = $(this).data('receipt-print');
            var frame = $('#receiptPrintFrame');

            if (!frame.length) {
                frame = $('<iframe id="receiptPrintFrame" title="Receipt"></iframe>')
                    .css({ position: 'fixed', width: 0, height: 0, border: 0, visibility: 'hidden' })
                    .appendTo('body');
            }

            frame.off('load').on('load', function () {
                try {
                    this.contentWindow.focus();
                    this.contentWindow.print();
                } catch (error) {
                    window.open(url, '_blank');
                }
            });

            frame.attr('src', url);
        });
    </script>
@endpush
