@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-a">
        <p class="rbi-a-heading">{{ __('rbi.form_a.title') }}</p>
        <p class="rbi-a-title">{{ __('rbi.form_a.subtitle') }}</p>

        <table class="rbi-a-meta">
            <tbody>
                @foreach (['region', 'province', 'city_municipality', 'barangay', 'household_address', 'no_of_household_members'] as $field)
                    <tr>
                        <td class="label">{{ __('rbi.form_a.fields.' . $field) }}</td>
                        <td class="fill">{{ data_get($data, $field) }}</td>
                        <td class="spacer"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-a-grid">
            <colgroup>
                <col style="width: 14.24%;">
                <col style="width: 12.44%;">
                <col style="width: 9.90%;">
                <col style="width: 4.27%;">
                <col style="width: 8.77%;">
                <col style="width: 9.07%;">
                <col style="width: 3.67%;">
                <col style="width: 4.27%;">
                <col style="width: 5.62%;">
                <col style="width: 6.60%;">
                <col style="width: 8.62%;">
                <col style="width: 12.53%;">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="4">{{ __('rbi.form_a.columns.name') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.birth_place') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.birth_date') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.age') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.sex') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.civil_status') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.citizenship') }}</th>
                    <th rowspan="2">{{ __('rbi.form_a.columns.occupation') }}</th>
                    <th rowspan="2" class="indicator">{{ __('rbi.form_a.columns.indicators') }}</th>
                </tr>
                <tr>
                    <th>{{ __('rbi.form_a.columns.last_name') }}</th>
                    <th>{{ __('rbi.form_a.columns.first_name') }}</th>
                    <th>{{ __('rbi.form_a.columns.middle_name') }}</th>
                    <th>{{ __('rbi.form_a.columns.extension_name') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach (data_get($data, 'members', []) as $member)
                    <tr>
                        @foreach (['last_name', 'first_name', 'middle_name', 'extension_name', 'birth_place', 'birth_date', 'age', 'sex', 'civil_status', 'citizenship', 'occupation'] as $column)
                            <td>{{ data_get($member, $column) }}</td>
                        @endforeach
                        <td class="indicator">{{ data_get($member, 'indicators') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-a-sign">
            <tbody>
                <tr>
                    <td class="head">{{ __('rbi.form_a.signatures.prepared_by') }}</td>
                    <td class="head">{{ __('rbi.form_a.signatures.certified') }}</td>
                    <td class="head">{{ __('rbi.form_a.signatures.validated') }}</td>
                </tr>
                <tr>
                    <td class="line"></td>
                    <td class="line"></td>
                    <td class="line"></td>
                </tr>
                <tr>
                    <td class="name">{{ __('rbi.form_a.signatures.head') }}</td>
                    <td class="name">{{ __('rbi.form_a.signatures.secretary') }}</td>
                    <td class="name">{{ __('rbi.form_a.signatures.captain') }}</td>
                </tr>
                <tr>
                    <td class="over">{{ __('rbi.form_a.signatures.over') }}</td>
                    <td class="over">{{ __('rbi.form_a.signatures.over') }}</td>
                    <td class="over">{{ __('rbi.form_a.signatures.over') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-a-note">{{ __('rbi.form_a.privacy') }}</p>
    </div>
@endsection
