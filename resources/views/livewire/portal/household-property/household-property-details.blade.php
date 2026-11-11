{{-- =================================================================== --}}
{{-- THE HOUSEHOLD PAGE                                                 --}}
{{-- Read top to bottom: the unit the household lives in, then the       --}}
{{-- household's own facts, then who lives there. The unit is the        --}}
{{-- office's to assign (display only); the household facts are the       --}}
{{-- homeowner's to keep, through the Edit Details dialog.               --}}
{{--                                                                     --}}
{{-- Empty values say why they are empty instead of showing a dash:      --}}
{{-- office-owned fields point to the office, homeowner-owned fields     --}}
{{-- point to Edit Details.                                              --}}
{{-- =================================================================== --}}
<div class="row">

    @if ($household)

        {{-- ============================================================= --}}
        {{-- 1. THE UNIT                                                    --}}
        {{-- ============================================================= --}}
        <div class="col-12">
            <x-adminlte-card title="My Unit" icon="fas fa-fw fa-home">
                @if ($property)
                    <div class="row align-items-center">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <img src="{{ $property->photo_url ?: asset('image/placeholder.jpg') }}"
                                alt="{{ $property->block_lot_label }}"
                                class="img-fluid rounded w-100" style="height: 200px; object-fit: cover;">
                        </div>

                        <div class="col-12 col-md-8">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted font-weight-normal">Block / Lot</dt>
                                <dd class="col-sm-8 font-weight-bold">
                                    {{ $property->block_lot_label ?: 'Not assigned yet' }}
                                </dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">Address</dt>
                                <dd class="col-sm-8">
                                    {{ $property->address?->full_address ?: 'No street address on file yet' }}
                                </dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">Unit Type</dt>
                                <dd class="col-sm-8">
                                    @if ($type)
                                        <span class="badge badge-{{ $type->badgeTheme() }}">{{ $type->label() }}</span>
                                    @else
                                        <span class="text-muted font-italic">Not yet classified by the office</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4 text-muted font-weight-normal mb-0">Floor Area</dt>
                                <dd class="col-sm-8 mb-0">
                                    @if ($property->sqm !== null)
                                        {{ number_format($property->sqm, 2) }} sqm
                                    @else
                                        <span class="text-muted font-italic">Not measured yet</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <p class="text-muted small mb-0 mt-3">
                        <i class="fas fa-fw fa-info-circle mr-1"></i>
                        The office assigns each account to a unit. Ask the office about anything that looks wrong.
                    </p>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-fw fa-home fa-2x mb-2"></i>
                        <p class="mb-1">No unit is on record for your account yet.</p>
                        <small>The office assigns each account to a unit — contact the office if this looks wrong.</small>
                    </div>
                @endif
            </x-adminlte-card>
        </div>

        {{-- ============================================================= --}}
        {{-- 2. THE HOUSEHOLD                                               --}}
        {{-- ============================================================= --}}
        <div class="col-12 col-lg-5">
            <x-adminlte-card title="Household Details" icon="fas fa-fw fa-house-user">

                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted font-weight-normal">Tenure Type</dt>
                    <dd class="col-sm-7">
                        @if ($occupancy)
                            <span class="badge badge-{{ $occupancy->badgeTheme() }}">{{ $occupancy->label() }}</span>
                        @else
                            <span class="text-muted font-italic">Not set yet</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5 text-muted font-weight-normal">Previous Address</dt>
                    <dd class="col-sm-7">
                        @if ($household->prev_address)
                            {{ $household->prev_address }}
                        @else
                            <span class="text-muted font-italic">None on file</span>
                        @endif
                    </dd>

                    <dt class="col-sm-5 text-muted font-weight-normal mb-0">Recorded</dt>
                    <dd class="col-sm-7 mb-0">
                        {{ $household->created_at ? $household->created_at->format('F j, Y') : 'Date was not logged' }}
                    </dd>
                </dl>

                @can('updateOwn', $household)
                    <div class="text-right mt-3">
                        <x-adminlte-button label="Edit Details" theme="primary" icon="fas fa-fw fa-pen"
                            data-toggle="modal" data-target="#editHouseholdModal" class="btn-sm" />
                    </div>
                @endcan

            </x-adminlte-card>
        </div>

        {{-- ============================================================= --}}
        {{-- 3. WHO LIVES HERE                                              --}}
        {{-- ============================================================= --}}
        <div class="col-12 col-lg-7">
            <x-adminlte-card title="Members ({{ $household->member_count }})" icon="fas fa-fw fa-users">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 3rem;">#</th>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Account</th>
                                <th class="text-right">Age</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($household->memberRecords as $member)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($member->is_head)
                                            <i class="fas fa-fw fa-crown text-warning mr-1" title="Head of the family"></i>
                                        @endif
                                        @if ($member->resident?->full_name)
                                            {{ $member->resident->full_name }}
                                        @else
                                            <span class="text-muted font-italic">Name not recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $member->relationship_label }}
                                        @if ($member->is_head)
                                            <span class="badge badge-success ml-1">Head</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($member->resident?->user)
                                            <span class="badge badge-info">Linked</span>
                                        @else
                                            <span class="text-muted small">No account</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($member->resident?->age !== null)
                                            {{ $member->resident->age }}
                                        @else
                                            <span class="text-muted font-italic">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No other members are recorded for this household.
                                        @can('updateOwn', $household)
                                            Use <strong>Manage Members</strong> to add them.
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('updateOwn', $household)
                    <div class="text-right mt-3">
                        <x-adminlte-button label="Manage Members" theme="primary" icon="fas fa-fw fa-user-cog"
                            data-toggle="modal" data-target="#manageMembersModal" data-id="{{ $household->id }}"
                            class="btn-sm" />
                    </div>
                @endcan
            </x-adminlte-card>
        </div>

        @livewire('portal.household-property.modals.edit-household-modal')
        @livewire('portal.household-property.modals.manage-members-modal')

    @else

        {{-- ============================================================= --}}
        {{-- NO HOUSEHOLD YET                                               --}}
        {{-- ============================================================= --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <x-adminlte-empty-state icon="fas fa-fw fa-users"
                        title="No Household on Record"
                        text="You may create your household here. Enter the block and lot, and you will be recorded as its household head." />

                    <x-adminlte-button label="Add Household" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#addHouseholdModal" class="btn-sm mt-3" />
                </div>
            </div>
        </div>

        @livewire('portal.household-property.modals.add-household-modal')

    @endif
</div>
