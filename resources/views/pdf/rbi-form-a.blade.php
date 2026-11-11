@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-a">
        <p class="rbi-a-title">{{ __('rbi.form_a.title') }}</p>
        <p class="rbi-a-subtitle">{{ __('rbi.form_a.subtitle') }}</p>

        <table class="rbi-a-meta">
            <tbody>
                @foreach (['region', 'province', 'city_municipality', 'barangay', 'household_address'] as $field)
                    <tr>
                        <th>{{ __('rbi.form_a.fields.' . $field) }}</th>
                        <td>{{ data_get($data, $field) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th>{{ __('rbi.form_a.fields.no_of_household_members') }}</th>
                    <td>{{ data_get($data, 'no_of_household_members') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="rbi-a-table">
            <thead>
                <tr>
                    <th colspan="4" class="rbi-a-name">{{ __('rbi.form_a.columns.name') }}</th>
                    @foreach (['birth_place', 'birth_date', 'age', 'sex', 'civil_status', 'citizenship', 'occupation'] as $column)
                        <th rowspan="2">{{ __('rbi.form_a.columns.' . $column) }}</th>
                    @endforeach
                    <th rowspan="2" class="rbi-a-indicator">{{ __('rbi.form_a.columns.indicators') }}</th>
                </tr>
                <tr class="rbi-a-name">
                    @foreach (['last_name', 'first_name', 'middle_name', 'extension_name'] as $column)
                        <th>{{ __('rbi.form_a.columns.' . $column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach (data_get($data, 'members', []) as $member)
                    <tr>
                        @foreach (['last_name', 'first_name', 'middle_name', 'extension_name', 'birth_place', 'birth_date', 'age', 'sex', 'civil_status', 'citizenship', 'occupation'] as $column)
                            <td>{{ data_get($member, $column) }}</td>
                        @endforeach
                        <td class="rbi-a-indicator">{{ data_get($member, 'indicators') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-a-sign">
            <tbody>
                <tr>
                    <td class="rbi-a-sign-head">{{ __('rbi.form_a.signatures.prepared_by') }}</td>
                    <td class="rbi-a-sign-head">{{ __('rbi.form_a.signatures.certified') }}</td>
                    <td class="rbi-a-sign-head">{{ __('rbi.form_a.signatures.validated') }}</td>
                </tr>
                <tr>
                    <td class="rbi-a-line"></td>
                    <td class="rbi-a-line"></td>
                    <td class="rbi-a-line"></td>
                </tr>
                <tr>
                    <td class="rbi-a-sign-name">{{ __('rbi.form_a.signatures.head') }}</td>
                    <td class="rbi-a-sign-name">{{ __('rbi.form_a.signatures.secretary') }}</td>
                    <td class="rbi-a-sign-name">{{ __('rbi.form_a.signatures.captain') }}</td>
                </tr>
                <tr>
                    <td>{{ __('rbi.form_a.signatures.over') }}</td>
                    <td>{{ __('rbi.form_a.signatures.over') }}</td>
                    <td>{{ __('rbi.form_a.signatures.over') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-a-note">{{ __('rbi.form_a.privacy') }}</p>
    </div>
@endsection
