<div wire:ignore.self>
    <x-adminlte-modal id="profileVerificationViewModal"
        title="{{ __('users.verification.modal_title') }}"
        icon="fas fa-fw fa-user-check" theme="info" size="xl" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the profile…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($verificationAccount)
                @php
                    $resident = $verificationAccount->resident;
                    $link = $resident?->householdLinkRequest;
                    $awaitingHead = (bool) $link?->isPending();
                    $household = $resident?->household ?? $link?->household;
                    $property = $household?->property;
                    $head = $household?->headMember?->resident;
                    $validIdUrl = $resident && $resident->valid_id_path
                        ? route('homeowners.residents.valid-id', $resident)
                        : null;

                    $v = fn ($value) => filled($value)
                        ? e($value)
                        : '<span class="text-muted font-italic">Not provided</span>';
                @endphp

                {{-- ===== Identity header ===== --}}
                <div class="card card-outline card-info mb-3">
                    <div class="card-body py-3">
                        <div class="d-flex flex-column flex-md-row align-items-md-center">
                            <img src="{{ $resident?->photo_url ?: asset('image/user.png') }}"
                                alt="{{ $resident?->full_name }}"
                                class="rounded-circle shadow-sm flex-shrink-0 align-self-center align-self-md-start"
                                style="width:84px; height:84px; object-fit:cover; border:3px solid #fff;">

                            <div class="ml-md-3 mt-3 mt-md-0 text-center text-md-left flex-grow-1">
                                <h5 class="font-weight-bold mb-0">{{ $verificationAccount->name }}</h5>
                                <div class="text-muted">{{ $verificationAccount->email }}</div>
                                <span class="badge badge-{{ $verificationAccount->profile_status->badgeTheme() }} mt-2">
                                    {{ $verificationAccount->profile_status->label() }}
                                </span>
                            </div>

                            <dl class="row small mb-0 mt-3 mt-md-0 ml-md-4 text-center text-md-left" style="min-width:260px;">
                                <dt class="col-5 text-muted font-weight-normal">Submitted</dt>
                                <dd class="col-7 mb-1">{!! $v($verificationAccount->profile_submitted_at?->format('M d, Y g:i A')) !!}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                @if ($awaitingHead)
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-fw fa-clock mr-1"></i>
                        {{ __('users.verification.awaiting_head') }}
                    </div>
                @endif

                {{-- Previous correction request, only when there is one --}}
                @if (filled($verificationAccount->profile_rejection_note))
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>
                        <strong>Correction previously requested:</strong>
                        {{ $verificationAccount->profile_rejection_note }}
                    </div>
                @endif

                {{-- ===== Personal + Contact ===== --}}
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0">
                                    <i class="fas fa-fw fa-id-card mr-1"></i>{{ __('users.verification.personal') }}
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <dl class="row small mb-0">
                                    <dt class="col-sm-5 text-muted font-weight-normal">Full Name</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->full_name) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Surname</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->surname) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">First Name</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->first_name) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Middle Name</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->middle_name) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Suffix</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->suffix) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">PhilSys Card No.</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->philsys_card_no) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Sex</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->sex_label) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Birth Date</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->birth_date?->format('M d, Y')) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Birth Place</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($resident?->birth_place) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Civil Status</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->civil_status_label) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Spouse Name</dt>
                                    <dd class="col-sm-7 mb-0 text-break">{!! $v($resident?->spouse_name) !!}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0">
                                    <i class="fas fa-fw fa-address-book mr-1"></i>{{ __('users.verification.contact') }}
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <dl class="row small mb-0">
                                    <dt class="col-sm-5 text-muted font-weight-normal">Email</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($verificationAccount->email) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Mobile No.</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->mobile_no) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Citizenship</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->citizenship) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Religion</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->religion) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Occupation</dt>
                                    <dd class="col-sm-7">{!! $v($resident?->occupation) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Educational Attainment</dt>
                                    <dd class="col-sm-7 mb-0">{!! $v($resident?->educational_attainment_label) !!}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Household + Valid ID ===== --}}
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0">
                                    <i class="fas fa-fw fa-home mr-1"></i>{{ __('users.verification.household') }}
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <dl class="row small mb-0">
                                    <dt class="col-sm-5 text-muted font-weight-normal">Address</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($property?->full_address_label) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Floor Area</dt>
                                    <dd class="col-sm-7">{!! $v($property && $property->sqm !== null ? number_format((float) $property->sqm, 2) . ' sqm' : null) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Unit Type</dt>
                                    <dd class="col-sm-7">{!! $v($property?->type_enum?->label() ?? $property?->type) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Tenure</dt>
                                    <dd class="col-sm-7">{!! $v($household?->tenure_label) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Household Head</dt>
                                    <dd class="col-sm-7 text-break">{!! $v($head?->full_name) !!}</dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Head Confirmation</dt>
                                    <dd class="col-sm-7 text-break">
                                        @if ($link)
                                            @if ($link->wantsHead())
                                                <div class="mb-1">{{ __('users.verification.head_requested') }}</div>
                                            @endif
                                            <span class="badge badge-{{ $link->status?->badgeTheme() ?? 'secondary' }}">
                                                {{ $link->status?->label() ?? 'No answer yet' }}
                                            </span>
                                            @if ($link->decided_at)
                                                <div class="text-muted">{{ $link->decided_at->format('M d, Y g:i A') }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted font-italic">Not required</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-5 text-muted font-weight-normal">Previous Address</dt>
                                    <dd class="col-sm-7 mb-0 text-break">{!! $v($household?->prev_address) !!}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0">
                                    <i class="fas fa-fw fa-id-badge mr-1"></i>{{ __('users.verification.valid_id') }}
                                </h6>
                            </div>
                            <div class="card-body py-3 text-center d-flex flex-column justify-content-center">
                                @if ($validIdUrl)
                                    @can('viewValidId', $resident)
                                        <a href="{{ $validIdUrl }}" target="_blank" rel="noopener" class="d-inline-block">
                                            <img src="{{ $validIdUrl }}" alt="{{ __('users.verification.preview_alt') }}"
                                                class="img-fluid img-thumbnail" style="max-height:240px;">
                                        </a>
                                        <p class="small mt-2 mb-0">
                                            <a href="{{ $validIdUrl }}" target="_blank" rel="noopener">
                                                <i class="fas fa-fw fa-search-plus mr-1"></i>{{ __('users.verification.preview_id') }}
                                            </a>
                                        </p>
                                    @else
                                        <span class="small text-muted font-italic">You don't have permission to view this ID.</span>
                                    @endcan
                                @else
                                    <span class="small text-muted font-italic">Not provided</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Correction note ===== --}}
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <label class="small font-weight-bold mb-1" for="verificationNote">
                            {{ __('users.verification.note') }}
                        </label>
                        <textarea id="verificationNote" class="form-control form-control-sm @error('note') is-invalid @enderror"
                            rows="2" maxlength="500" wire:model.defer="note"></textarea>
                        @error('note')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">{{ __('users.verification.note_hint') }}</small>
                    </div>
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="{{ __('users.verification.close') }}" theme="outline-secondary"
                data-dismiss="modal" class="btn-sm mr-auto" />

            @if ($verificationAccount)
                <button type="button" class="btn btn-danger btn-sm" wire:click="reject"
                    wire:loading.attr="disabled" wire:target="reject">
                    <span wire:loading.remove wire:target="reject">
                        <i class="fas fa-fw fa-undo mr-1"></i>{{ __('users.verification.reject') }}
                    </span>
                    <span wire:loading wire:target="reject">
                        <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>{{ __('users.verification.rejecting') }}
                    </span>
                </button>

                <button type="button" class="btn btn-success btn-sm" wire:click="approve"
                    wire:loading.attr="disabled" wire:target="approve" {{ $awaitingHead ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="approve">
                        <i class="fas fa-fw fa-check mr-1"></i>{{ __('users.verification.approve') }}
                    </span>
                    <span wire:loading wire:target="approve">
                        <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>{{ __('users.verification.approving') }}
                    </span>
                </button>
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
