<div>
    {{-- =================================================================== --}}
    {{-- HEADER + FILE BUTTON                                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-8 mb-2 mb-sm-0">
                <h5 class="mb-0 font-weight-bold">Requests</h5>
                <small class="text-muted">
                    Documents you ask the association's office to issue — certifications, permits and endorsement
                    letters.
                </small>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @if ($canFile)
                    <x-adminlte-button label="File a Request" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#requestFileModal" class="btn-sm btn-block" />
                @endif
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- REQUEST CARDS                                                       --}}
    {{-- =================================================================== --}}
    <div class="row">
        @forelse ($requests as $request)
            <div class="col-12 col-md-6 col-lg-4 mb-3" wire:key="request-{{ $request->id }}">
                <div class="card h-100 mb-0 shadow-sm">
                    <div class="card-header bg-white px-3 py-2">
                        <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                            <div class="min-width-0">
                                <div class="font-weight-bold text-break">{{ $request->type_label }}</div>
                                <small class="text-muted">
                                    <i class="far fa-fw fa-calendar-alt mr-1"></i>
                                    Filed {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : '—' }}
                                </small>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="badge badge-{{ $request->status_badge_theme }}">
                                    {{ $request->status_label }}
                                </span>
                                @if ($request->has_bill_waiting)
                                    <div class="mt-1">
                                        <span class="badge badge-warning">
                                            <i class="fas fa-fw fa-file-invoice-dollar"></i> Bill waiting
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-3 py-3">
                        @foreach ($request->details() as $label => $value)
                            <div class="d-flex justify-content-between align-items-start mb-1" style="gap:0.75rem;">
                                <div class="text-muted small flex-shrink-0">{{ $label }}</div>
                                <div class="small font-weight-medium text-right text-break">{{ $value }}</div>
                            </div>
                        @endforeach

                        @if ($request->is_processed)
                            <div class="alert {{ $request->status_badge_theme === 'danger' ? 'alert-danger' : 'alert-success' }} border mt-3 mb-0 small">
                                <div class="font-weight-bold mb-1">
                                    <i class="fas fa-fw {{ $request->status_badge_theme === 'danger' ? 'fa-times-circle' : 'fa-check-circle' }} mr-1"></i>
                                    {{ $request->status_label }}
                                    @if ($request->processed_at)
                                        · {{ $request->processed_at->format('M j, Y') }}
                                    @endif
                                </div>
                                @if ($request->remarks)
                                    <p class="mb-0">{{ $request->remarks }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 pt-3 border-top">
                            @if ($request->is_approved)
                                <a href="{{ route('portal.requests.slip', $request->id) }}"
                                    class="btn btn-sm btn-outline-secondary btn-block">
                                    <i class="fas fa-fw fa-file-pdf mr-1"></i> {{ __('slips.download') }}
                                </a>
                                <div class="text-muted small mt-1 text-center">{{ __('slips.printable') }}</div>
                            @endif

                            @can('deleteOwn', $request)
                                <x-adminlte-button label="Delete Request" theme="outline-danger"
                                    icon="fas fa-fw fa-trash" data-toggle="modal"
                                    data-target="#requestWithdrawModal" data-id="{{ $request->id }}"
                                    class="btn-sm btn-block" />
                            @endcan

                            @if ($request->has_bill_waiting)
                                <div class="alert alert-warning border mb-0 mt-2 small">
                                    <i class="fas fa-fw fa-file-invoice-dollar mr-1"></i>
                                    {{ __('slips.bill_waiting', ['reference' => $request->bill->reference]) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <x-adminlte-empty-state icon="far fa-fw fa-file-alt"
                            title="No Requests Yet" />
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
