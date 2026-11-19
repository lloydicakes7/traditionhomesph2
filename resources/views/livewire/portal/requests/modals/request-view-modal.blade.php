<div wire:ignore.self>
    <x-adminlte-modal id="requestViewModal" title="Request Details"
        icon="fas fa-fw fa-clipboard" theme="info" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                <div class="d-flex justify-content-between align-items-start mb-3" style="gap:0.75rem;">
                    <div class="min-width-0">
                        <h6 class="mb-0 font-weight-bold text-break">{{ $request->type_label }}</h6>
                        <small class="text-muted d-block">{{ $request->reference ?: 'Not recorded' }}</small>
                        <small class="text-muted">
                            Filed {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : 'Not recorded' }}
                        </small>
                    </div>
                    <span class="badge badge-{{ $request->status_badge_theme }} flex-shrink-0">
                        {{ $request->status_label }}
                    </span>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">Homeowner</div>
                    <div class="font-weight-medium text-break">{{ $request->resident_label }}</div>
                </div>

                @if ($request->invoice_label)
                    <div class="mb-2">
                        <div class="text-muted small">Invoice</div>
                        <div class="font-weight-medium text-break">{{ $request->invoice_label }}</div>
                    </div>
                @endif

                <div class="row">
                    @foreach ($request->details() as $label => $value)
                        <div class="col-12 col-md-6 mb-2">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="font-weight-medium text-break">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>

                @if ($request->reason)
                    <div class="mt-2 pt-2 border-top">
                        <div class="text-muted small">Reason for Request</div>
                        <p class="mb-0 text-break">{{ $request->reason }}</p>
                    </div>
                @endif

                @if ($request->has_claiming_schedule)
                    <div class="alert alert-success border mt-3 mb-0">
                        <div class="font-weight-bold small mb-2">
                            <i class="far fa-fw fa-calendar-check mr-1"></i>Claiming Schedule
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-muted small">Claiming Date</div>
                                <div class="font-weight-medium">{{ $request->claiming_date_label }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Claiming Time</div>
                                <div class="font-weight-medium">{{ $request->claiming_time_label }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($request->is_processed)
                    <div class="alert {{ $request->status_badge_theme === 'danger' ? 'alert-danger' : 'alert-light' }} border mt-3 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-user-check mr-1"></i>
                            {{ $request->status_label }}
                            {{ $request->processed_at ? $request->processed_at->format('M j, Y g:i A') : '' }}
                        </div>
                        @if ($request->remarks)
                            <p class="mb-0 small">{{ $request->remarks }}</p>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            @if ($request)
                @can('downloadSlipOwn', $request)
                    <a href="{{ route('portal.requests.slip', $request->id) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-fw fa-file-pdf mr-1"></i> {{ __('slips.download') }}
                    </a>
                @endcan
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
