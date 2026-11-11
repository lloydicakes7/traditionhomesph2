@props([
    'profile' => null,
    'supply' => false,
])

{{--
    The Membership Form, read back from the records on file.

    The form is never typed in by the homeowner: its personal details come from
    the resident profile, the requestee type and previous address from the
    household, the block and lot from the unit, and the household table from the
    household's own members. This fragment shows exactly what the printed sheet
    will say, and names the blanks the office still has to complete on those
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
            <div class="font-weight-bold mb-1">
                <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>
                The form is missing information on file
            </div>
            <p class="mb-0">
                This form is filled in from your household's records, and the HOA office first has to
                complete: {{ implode(', ', $profile['missing']) }}.
            </p>
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
                    <div class="font-weight-medium text-break">{{ $value !== '' ? $value : '—' }}</div>
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
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Birthdate</th>
                            <th>Birthplace</th>
                            <th>Relationship</th>
                            <th>Occupation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td class="text-break">{{ $member['name'] }}</td>
                                <td>{{ $member['age'] !== '' ? $member['age'] : '—' }}</td>
                                <td>{{ $member['birth_date'] !== '' ? $member['birth_date'] : '—' }}</td>
                                <td class="text-break">{{ $member['birth_place'] !== '' ? $member['birth_place'] : '—' }}</td>
                                <td>{{ $member['relation'] !== '' ? $member['relation'] : '—' }}</td>
                                <td class="text-break">{{ $member['occupation'] !== '' ? $member['occupation'] : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mb-0 small text-muted">No other household members are on file.</p>
        @endif

        <div class="mt-2 small">
            <span class="text-muted">Name of the Owner:</span>
            <span class="font-weight-medium">{{ $data['owner_name'] !== '' ? $data['owner_name'] : '—' }}</span>
        </div>
    </div>
@endif
