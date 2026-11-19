@props([
    'profile' => null,
    'supply' => false,
    'prefix' => 'requestType',
])

{{--
    The Membership Form, read back from the records on file.

    The form is never typed in by the homeowner: its personal details come from
    the resident profile, the requestee type and previous address from the
    household, the block and lot from the unit, and the household members from
    the household's own records. This fragment shows exactly what the printed
    sheet will say, and names the blanks the office still has to complete on those
    records — nothing here is submitted, so nothing here can disagree with them.

    The office's own dialogs may also supply, for one form only, a detail the
    record leaves blank (`supply`). The homeowner's form never asks: the office
    completes the record instead, so the portal stays read-only.

    Data: App\Queries\Support\Requests\RequestProfileQuery.
--}}

@if ($profile && ! empty($profile['profile']))
    @php
        $data = $profile['profile'];

        $rows = [
            'Date'                           => $data['date']['long'],
            'Requestee Type'                 => $data['member_type'],
            'Surname'                        => $data['surname'],
            'First Name'                     => $data['first_name'],
            'Middle Name'                    => $data['middle_name'],
            'Religion'                       => $data['religion'],
            'Sex'                            => $data['sex'],
            'Citizenship'                    => $data['citizenship'],
            'Age'                            => $data['age'],
            'Birthdate'                      => $data['birth_date']['long'],
            'Birthplace'                     => $data['birth_place'],
            'Civil Status'                   => $data['civil_status'],
            'Spouse Name'                    => $data['spouse_name'],
            'Block'                          => $data['block'],
            'Lot'                            => $data['lot'],
            'Previous Address'               => $data['previous_address'],
            'Highest Educational Attainment' => $data['education'],
            'Occupation'                     => $data['occupation'],
            'Mobile Phone Number'            => $data['mobile'],
        ];

        if ($data['civil_status'] !== 'Married') {
            unset($rows['Spouse Name']);
        }

        $members = array_filter($data['members'], function (array $member) {
            return $member['name'] !== '';
        });
    @endphp

    {{-- WHAT THE OFFICE STILL HAS TO COMPLETE --}}
    @if (! $profile['ready'])
        <div class="alert alert-warning border small">
            <div class="d-flex align-items-center justify-content-between" style="gap:0.75rem;">
                <div>
                    <div class="font-weight-bold mb-1">
                        <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>
                        Personal information is incomplete
                    </div>
                    <p class="mb-0">
                        @if ($supply)
                            The record still needs: {{ implode(', ', $profile['missing']) }}.
                        @else
                            Complete the required details on your Personal Information page before filing this request.
                        @endif
                    </p>
                </div>

                @unless ($supply)
                    <a href="{{ route('portal.personal-information') }}" class="btn btn-sm btn-primary flex-shrink-0">
                        Add
                    </a>
                @endunless
            </div>
        </div>
    @endif

    {{-- WHAT THE RECORD DOES NOT CARRY — OPTIONAL, AND FOR THIS FORM ONLY --}}
    @if ($supply && ! empty($profile['missing_fields']))
        <div class="border rounded p-2 mb-3">
            <div class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-2">
                <i class="fas fa-fw fa-pen mr-1"></i> Missing on the record — optional
            </div>

            <p class="small text-muted mb-2">
                These details are not on your household's records. Fill in what you can: they are used
                for this form only and are never written back to your profile. Anything left blank
                stays blank, and the HOA office completes the rest.
            </p>

            <div class="row">
                @foreach ($profile['missing_fields'] as $key => $label)
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="membership_{{ $key }}" label="{{ $label }}"
                            wire:model.defer="form.membership.{{ $key }}" igroup-size="sm" />
                        @error('form.membership.' . $key)
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PERSONAL INFORMATION --}}
    <div class="border rounded p-2 mb-3">
        <div class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-2">
            <i class="fas fa-fw fa-id-card mr-1"></i> Membership Form details
        </div>

        <div class="row">
            @foreach ($rows as $label => $value)
                <div class="col-12 col-md-6 mb-1">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="font-weight-medium text-break">{{ $value !== '' ? $value : 'Not recorded' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- HOUSEHOLD MEMBERS --}}
    <div class="border rounded p-2">
        <div class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-2">
            <i class="fas fa-fw fa-users mr-1"></i> Household Members
        </div>

        @if (count($members))
            <div class="thha-household-member-accordion">
                @foreach ($members as $member)
                    @php
                        $memberId = $prefix . '_household_member_' . $loop->index;
                    @endphp

                    <div class="border rounded mb-2 thha-household-member">
                        <button type="button"
                            id="{{ $memberId }}_header"
                            class="thha-household-member-toggle collapsed"
                            data-toggle="collapse"
                            data-target="#{{ $memberId }}"
                            aria-expanded="false"
                            aria-controls="{{ $memberId }}">
                            <span class="flex-grow-1 mr-2" style="min-width:0;">
                                <span class="font-weight-bold text-break d-block">
                                    {{ $member['name'] !== '' ? $member['name'] : 'Not recorded' }}
                                </span>
                                <span class="small text-muted text-break d-block">
                                    {{ $member['relation'] !== '' ? $member['relation'] : 'Not recorded' }}
                                </span>
                            </span>
                            <i class="fas fa-fw fa-chevron-down thha-household-member-chevron text-muted flex-shrink-0"
                                aria-hidden="true"></i>
                        </button>

                        <div id="{{ $memberId }}" class="collapse" aria-labelledby="{{ $memberId }}_header">
                            <div class="border-top px-3 py-2">
                                <div class="row">
                                    @foreach ([
                                        'Age'          => $member['age'],
                                        'Birthdate'    => $member['birth_date'],
                                        'Birthplace'   => $member['birth_place'],
                                        'Relationship' => $member['relation'],
                                        'Occupation'   => $member['occupation'],
                                    ] as $label => $value)
                                        <div class="col-12 col-sm-6 mb-2">
                                            <div class="text-muted small">{{ $label }}</div>
                                            <div class="font-weight-medium text-break">{{ $value !== '' ? $value : 'Not recorded' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mb-0 small text-muted">No other household members are on file.</p>
        @endif

        <div class="mt-2 small">
            <span class="text-muted">Name of the Owner:</span>
            <span class="font-weight-medium">{{ $data['owner_name'] !== '' ? $data['owner_name'] : 'Not recorded' }}</span>
        </div>
    </div>
@endif
