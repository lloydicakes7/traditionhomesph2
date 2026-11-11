<div wire:ignore.self>
    <x-adminlte-modal id="requestViewModal" title="Request Details"
        icon="fas fa-fw fa-clipboard" theme="info" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                {{-- HEADER: what it is, and where it stands --}}
                <div class="d-flex justify-content-between align-items-start mb-3" style="gap:0.75rem;">
                    <div class="min-width-0">
                        <h6 class="mb-0 font-weight-bold text-break">{{ $request->type_label }}</h6>
                        <small class="text-muted d-block">{{ $request->reference ?: '—' }}</small>
                        <small class="text-muted">
                            Filed {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : '—' }}
                        </small>
                    </div>
                    <span class="badge badge-{{ $request->status_badge_theme }} flex-shrink-0">
                        {{ $request->status_label }}
                    </span>
                </div>

                {{-- WHO FILED IT --}}
                <div class="mb-2">
                    <div class="text-muted small">Homeowner</div>
                    <div class="font-weight-medium text-break">{{ $request->resident_label }}</div>
                </div>

                {{-- THE INVOICE, once the office has raised one — so a reader
                     can see the request has been billed and stop offering to
                     bill it again. --}}
                @if ($request->invoice_label)
                    <div class="mb-2">
                        <div class="text-muted small">Invoice</div>
                        <div class="font-weight-medium text-break">{{ $request->invoice_label }}</div>
                    </div>
                @endif

                {{-- WHAT THE REQUEST ASKS FOR, in its own type's fields --}}
                <div class="row">
                    @foreach ($request->details() as $label => $value)
                        <div class="col-12 col-md-6 mb-2">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="font-weight-medium text-break">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- HOW IT WAS ANSWERED, ONCE IT HAS BEEN --}}
                @if ($request->is_processed)
                    <div class="alert alert-light border mt-2 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-user-check mr-1"></i>
                            {{ $request->status_label }}
                            {{ $request->processed_at ? $request->processed_at->format('M j, Y g:i A') : '' }}
                            @if ($request->processedByAdmin)
                                — {{ $request->processedByAdmin->name }}
                            @endif
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
            {{-- The document is issued once the request has been approved,
                 which is the point of approving it — the same rule the row's
                 actions follow. --}}
            @if ($request && $request->is_approved && $request->printDocument())
                @can('print.viewAny')
                    <a href="{{ route('print.show', ['document' => $request->printDocument()->slug(), 'request' => $request->id]) }}"
                        class="btn btn-sm btn-secondary">
                        <i class="fas fa-fw fa-print mr-1"></i> Print
                    </a>
                @endcan
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
