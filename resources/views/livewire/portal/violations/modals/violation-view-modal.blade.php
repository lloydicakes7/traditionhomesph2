<div wire:ignore.self>
    <x-adminlte-modal id="violationViewModal" title="Notice of Violation"
        icon="fas fa-fw fa-exclamation-triangle" theme="{{ $violation && $violation->status ? $violation->status->badgeTheme() : 'secondary' }}"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the notice…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        @if ($loaded && $violation)
        {{-- =============================================================== --}}
        {{-- HEADER: what it is, and where it stands                          --}}
        {{-- ======================md========================================= --}}
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="mb-0 font-weight-bold">{{ $violation->type_label }}</h6>
                <small class="text-muted">
                    <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $violation->occurred_display }}
                </small>
            </div>
            <span class="badge badge-{{ $violation->status_badge_theme }}">
                {{ $violation->status_label }}
            </span>
        </div>

        {{-- =============================================================== --}}
        {{-- THE FACTS                                                        --}}
        {{-- =============================================================== --}}
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <div class="text-muted small">Location</div>
                <div class="font-weight-medium">{{ $violation->location }}</div>
            </div>
            <div class="col-12 col-md-6 mb-2">
                <div class="text-muted small">Issued By</div>
                <div class="font-weight-medium">{{ $violation->issuedByAdmin->name ?? 'The HOA Office' }}</div>
            </div>
        </div>

        {{-- =============================================================== --}}
        {{-- WHAT THE NOTICE SAYS                                             --}}
        {{-- =============================================================== --}}
        <div class="mt-2">
            <div class="text-muted small mb-1">Description</div>
            <p class="mb-0">{{ $violation->description }}</p>
        </div>

        {{-- =============================================================== --}}
        {{-- HOW IT WAS CLOSED, ONCE IT HAS BEEN                              --}}
        {{-- =============================================================== --}}
        @if ($violation->resolved_at)
            <div class="alert alert-success border mt-3 mb-0">
                <div class="font-weight-bold small mb-1">
                    <i class="fas fa-fw fa-check-circle mr-1"></i>Resolved
                    {{ $violation->resolved_at->format('M j, Y') }}
                </div>
                @if ($violation->resolution_notes)
                    <p class="mb-0 small">{{ $violation->resolution_notes }}</p>
                @endif
            </div>
        @endif

        {{-- =============================================================== --}}
        {{-- ACKNOWLEDGMENT — what it means, said where it is recorded        --}}
        {{-- =============================================================== --}}
        <div class="alert alert-info mt-3 mb-0 small">
            <i class="fas fa-fw fa-info-circle mr-1"></i>
            @if ($violation->is_acknowledged)
                You acknowledged this notice on
                {{ $violation->acknowledged_at->format('M j, Y g:i A') }}. Acknowledging confirms you received
                and read it — it is not an admission that you agree with the violation.
            @else
                Acknowledging confirms you received and read this notice. It is <strong>not</strong> an admission
                that you agree with the violation.
            @endif
        </div>
        @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
