<div wire:ignore.self>
    <x-adminlte-modal id="residentViewModal"
        title="{{ $viewResident->full_name ?? 'Resident Details' }}"
        icon="fas fa-fw fa-user" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading resident details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($viewResident)
                <div class="text-center mb-4">
                    <img src="{{ $viewResident->photo_url ?: asset('image/user.png') }}"
                        class="rounded-circle shadow-sm thha-view-photo">
                </div>

                <h6 class="font-weight-bold mb-2"><i class="fas fa-fw fa-id-card mr-1"></i>Personal Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">PhilSys Card No.</th><td>{{ $viewResident->philsys_card_no ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Full Name</th><td class="font-weight-medium">{{ $viewResident->full_name }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Surname</th><td>{{ $viewResident->surname ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">First Name</th><td>{{ $viewResident->first_name ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Middle Name</th><td>{{ $viewResident->middle_name ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Suffix</th><td>{{ $viewResident->suffix ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Sex</th><td>{{ $viewResident->sex_label }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Birth Date</th><td>{{ $viewResident->birth_date?->format('M d, Y') ?? 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Age</th><td>{{ $viewResident->age ?? 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Birth Place</th><td>{{ $viewResident->birth_place ?: 'Not provided' }}</td></tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Civil Status</th><td>{{ $viewResident->civil_status_label }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Spouse Name</th><td>{{ $viewResident->spouse_name ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Citizenship</th><td>{{ $viewResident->citizenship ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Religion</th><td>{{ $viewResident->religion ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Occupation</th><td>{{ $viewResident->occupation ?: 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Educational Attainment</th><td>{{ $viewResident->educational_attainment_label }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Mobile No.</th><td>{{ $viewResident->mobile_no ?: 'Not provided' }}</td></tr>
                            <tr>
                                <th scope="row" class="thha-view-label text-muted font-weight-normal">Sectoral Tags</th>
                                <td>
                                    @forelse ($viewResident->sectorValues() as $sector)
                                        <span class="badge badge-info mr-1 mb-1">
                                            {{ \App\Enums\SectorType::tryFrom((string) $sector)?->label() ?? $sector }}
                                        </span>
                                    @empty
                                        <span class="text-muted">None recorded</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="thha-view-label text-muted font-weight-normal">Valid ID</th>
                                <td>
                                    @if ($viewResident->valid_id_path)
                                        @can('viewValidId', $viewResident)
                                            <a href="{{ route('homeowners.residents.valid-id', $viewResident) }}"
                                                target="_blank" rel="noopener" class="btn btn-outline-primary btn-xs">
                                                <i class="fas fa-fw fa-eye mr-1"></i>Preview
                                            </a>
                                        @else
                                            —
                                        @endcan
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Recorded</th><td>{{ $viewResident->created_at?->format('M d, Y g:i A') ?? 'Not provided' }}</td></tr>
                            <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Last Updated</th><td>{{ $viewResident->updated_at?->format('M d, Y g:i A') ?? 'Not provided' }}</td></tr>
                        </table>
                    </div>
                </div>

                <h6 class="font-weight-bold mb-2 mt-3"><i class="fas fa-fw fa-user-circle mr-1"></i>Account Information</h6>
                <table class="table table-sm table-borderless">
                    <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Email</th><td>{{ $viewResident->user?->email ?? 'Not provided' }}</td></tr>
                    <tr>
                        <th scope="row" class="thha-view-label text-muted font-weight-normal">Account Status</th>
                        <td>
                            @if ($viewResident->user && $viewResident->user->is_active)
                                <span class="badge badge-success">Active</span>
                            @elseif ($viewResident->user && ! $viewResident->user->is_active)
                                <span class="badge badge-warning">Inactive</span>
                            @else
                                <span class="badge badge-secondary">No Account</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th scope="row" class="thha-view-label text-muted font-weight-normal">Linked Since</th><td>{{ $viewResident->user?->created_at?->format('M d, Y') ?? 'Not provided' }}</td></tr>
                </table>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
