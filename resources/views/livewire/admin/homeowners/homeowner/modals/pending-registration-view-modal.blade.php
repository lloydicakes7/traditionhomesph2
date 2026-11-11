<div wire:ignore.self>
    <x-adminlte-modal id="pendingRegistrationViewModal"
        title="{{ $registration->resident?->full_name ?? 'Registration Details' }}"
        icon="fas fa-fw fa-user-clock" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading registration…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        @if ($registration)
            @php($resident = $registration->resident)

            <div class="text-center mb-4">
                @if ($resident?->photo_url)
                    <img src="{{ $resident->photo_url }}" class="rounded-circle shadow-sm"
                        style="width:96px; height:96px; object-fit:cover; border:3px solid #fff;" loading="lazy">
                @else
                    <img src="{{ asset('image/user.png') }}" class="rounded-circle shadow-sm"
                        style="width:96px; height:96px; object-fit:cover; border:3px solid #fff;" loading="lazy">
                @endif
                <div class="mt-2">
                    <span class="badge badge-warning">Waiting for approval</span>
                </div>
            </div>

            <div class="row">
                {{-- =========================================================== --}}
                {{-- PERSONAL INFORMATION                                        --}}
                {{-- =========================================================== --}}
                <div class="col-12 col-lg-6">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-fw fa-id-card mr-1"></i>Personal Information
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" style="width:45%;">Full Name</td><td>{{ $resident?->full_name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Sex</td><td>{{ $resident?->sex_label ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Birth Date</td><td>{{ $resident?->birth_date?->format('M d, Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Birth Place</td><td>{{ $resident?->birth_place ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Civil Status</td><td>{{ $resident?->civil_status_label ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Mobile No.</td><td>{{ $resident?->mobile_no ?? '—' }}</td></tr>
                        <tr><td class="text-muted">PhilSys Card No.</td><td>{{ $resident?->philsys_display ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Citizenship</td><td>{{ $resident?->citizenship ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Religion</td><td>{{ $resident?->religion ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Occupation</td><td>{{ $resident?->occupation ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Educational Attainment</td><td>{{ $resident?->educational_attainment_label ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Sectors</td><td>{{ $resident ? ($resident->sectorValues() ? implode(', ', $resident->sectorValues()) : '—') : '—' }}</td></tr>
                    </table>
                </div>

                {{-- =========================================================== --}}
                {{-- ACCOUNT                                                     --}}
                {{-- =========================================================== --}}
                <div class="col-12 col-lg-6">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-fw fa-user-circle mr-1"></i>Account
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted" style="width:45%;">Email</td><td class="text-break">{{ $registration->email }}</td></tr>
                        <tr><td class="text-muted">Registered</td><td>{{ $registration->created_at?->format('M d, Y h:i A') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Terms Accepted</td><td>{{ $registration->terms_accepted_at?->format('M d, Y') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Privacy Accepted</td><td>{{ $registration->privacy_accepted_at?->format('M d, Y') ?? '—' }}</td></tr>
                        <tr>
                            <td class="text-muted">Valid ID</td>
                            <td>
                                @if ($resident?->valid_id_path)
                                    @can('viewValidId', $resident)
                                        <a href="{{ route('homeowners.residents.valid-id', $resident) }}"
                                            class="btn btn-outline-primary btn-xs">
                                            <i class="fas fa-fw fa-download mr-1"></i>View Valid ID
                                        </a>
                                    @else
                                        <span class="text-muted">Restricted</span>
                                    @endcan
                                @else
                                    <span class="text-muted">Not uploaded</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <h6 class="font-weight-bold mb-2 mt-3">
                        <i class="fas fa-fw fa-home mr-1"></i>Unit &amp; Household
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width:45%;">Property</td>
                            <td>{{ $registration->household?->property?->block_lot_label ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address</td>
                            <td>{{ $registration->household?->property?->address?->full_address ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
