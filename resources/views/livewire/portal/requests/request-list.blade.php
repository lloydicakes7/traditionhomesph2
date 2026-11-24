<div>
    {{-- =================================================================== --}}
    {{-- HEADER + FILE BUTTON                                                --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-8 mb-2 mb-sm-0">
                <h5 class="mb-0 font-weight-bold">Requests</h5>
                <small class="text-muted">
                    Documents you ask the association's office to issue: certifications, permits and endorsement
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
                <x-portal.record-card :title="$request->type_label"
                    :meta="'Filed ' . ($request->created_at ? $request->created_at->format('M j, Y g:i A') : 'Not recorded')"
                    :status-theme="$request->status_badge_theme"
                    :status-label="$request->status_label"
                    :details="$request->details()"
                    :extra-badge="$request->has_bill_waiting ? 'Bill waiting' : null"
                    extra-badge-icon="fas fa-fw fa-file-invoice-dollar">
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

                    @if ($request->has_bill_waiting)
                        <div class="alert alert-warning border mb-0 mt-2 small">
                            <i class="fas fa-fw fa-file-invoice-dollar mr-1"></i>
                            {{ __('slips.bill_waiting', ['reference' => $request->bill->reference]) }}
                        </div>
                    @endif

                    <x-slot name="actions">
                        @if ($request->is_approved)
                            <a href="{{ route('portal.requests.slip', $request->id) }}"
                                class="btn btn-sm btn-info"
                                title="{{ __('slips.download_title') }}"
                                aria-label="{{ __('slips.download') }}">
                                <i class="fas fa-fw fa-file-pdf"></i>
                            </a>
                        @endif

                        <x-adminlte-button theme="secondary" icon="fas fa-fw fa-eye"
                            title="View request" aria-label="View request" data-toggle="modal"
                            data-target="#requestViewModal" data-id="{{ $request->id }}"
                            class="btn-sm" />

                        @can('updateOwn', $request)
                            <x-adminlte-button theme="primary" icon="fas fa-fw fa-edit"
                                title="Edit request" aria-label="Edit request" data-toggle="modal"
                                data-target="#requestEditModal" data-id="{{ $request->id }}"
                                class="btn-sm" />
                        @endcan

                        @can('deleteOwn', $request)
                            <x-adminlte-button theme="danger" icon="fas fa-fw fa-trash"
                                title="Delete request" aria-label="Delete request" data-toggle="modal"
                                data-target="#requestWithdrawModal" data-id="{{ $request->id }}"
                                class="btn-sm" />
                        @endcan
                    </x-slot>
                </x-portal.record-card>
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
