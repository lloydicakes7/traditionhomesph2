@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-c">
        <p class="rbi-c-heading">{{ __('rbi.form_c.title') }}</p>
        <p class="rbi-c-title">{{ __('rbi.form_c.subtitle') }}</p>
        <p class="rbi-c-term">for {{ data_get($data, 'semester') }} Semester of CY {{ data_get($data, 'year') }}</p>

        <table class="rbi-c-meta">
            <colgroup>
                <col style="width: 30.6%;">
                <col style="width: 10.8%;">
                <col style="width: 10.5%;">
                <col style="width: 14.35%;">
                <col style="width: 33.75%;">
            </colgroup>
            <tbody>
                @foreach (['region', 'province', 'city_municipality', 'barangay'] as $field)
                    <tr>
                        <td class="label">{{ __('rbi.form_c.fields.' . $field) }}</td>
                        <td class="fill" colspan="3">{{ data_get($data, $field) }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-c-meta rbi-c-figures">
            <colgroup>
                <col style="width: 30.6%;">
                <col style="width: 10.8%;">
                <col style="width: 10.5%;">
                <col style="width: 14.35%;">
                <col style="width: 33.75%;">
            </colgroup>
            <tbody>
                @foreach (['total_inhabitants', 'total_households', 'total_families'] as $field)
                    <tr>
                        <td class="label">{{ __('rbi.form_c.fields.' . $field) }}</td>
                        <td class="fill" colspan="3">{{ data_get($data, $field) }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-c-table">
            <colgroup>
                <col style="width: 30.6%;">
                <col style="width: 10.8%;">
                <col style="width: 10.5%;">
                <col style="width: 14.35%;">
                <col style="width: 33.75%;">
            </colgroup>
            <thead>
                <tr>
                    <th>{{ __('rbi.form_c.columns.indicator') }}</th>
                    <th>{{ __('rbi.form_c.columns.male') }}</th>
                    <th>{{ __('rbi.form_c.columns.female') }}</th>
                    <th>{{ __('rbi.form_c.columns.total') }}</th>
                    <th>{{ __('rbi.form_c.columns.remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section">
                    <td colspan="5">{{ __('rbi.form_c.age_title') }}</td>
                </tr>
                @foreach (__('rbi.form_c.age_brackets') as $key => $label)
                    <tr>
                        <td class="indicator-label">{{ $label }}</td>
                        <td class="number">{{ data_get($data, 'age_brackets.' . $key . '.male') }}</td>
                        <td class="number">{{ data_get($data, 'age_brackets.' . $key . '.female') }}</td>
                        <td class="number">{{ data_get($data, 'age_brackets.' . $key . '.total') }}</td>
                        <td class="remarks">{{ data_get($data, 'age_brackets.' . $key . '.remarks') }}</td>
                    </tr>
                @endforeach

                <tr class="section">
                    <td colspan="5">{{ __('rbi.form_c.sector_title') }}</td>
                </tr>
                @foreach (__('rbi.form_c.sectors') as $key => $label)
                    <tr>
                        <td class="indicator-label">{{ $label }}</td>
                        <td class="number">{{ data_get($data, 'sectors.' . $key . '.male') }}</td>
                        <td class="number">{{ data_get($data, 'sectors.' . $key . '.female') }}</td>
                        <td class="number">{{ data_get($data, 'sectors.' . $key . '.total') }}</td>
                        <td class="remarks">{{ data_get($data, 'sectors.' . $key . '.remarks') }}</td>
                    </tr>
                @endforeach

                @foreach (['civil_status', 'citizenship'] as $group)
                    @foreach (__('rbi.form_c.' . $group) as $key => $label)
                        <tr>
                            <td class="indicator-label">{{ $label }}</td>
                            <td class="number">{{ data_get($data, $group . '.' . $key . '.male') }}</td>
                            <td class="number">{{ data_get($data, $group . '.' . $key . '.female') }}</td>
                            <td class="number">{{ data_get($data, $group . '.' . $key . '.total') }}</td>
                            <td class="remarks">{{ data_get($data, $group . '.' . $key . '.remarks') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <table class="rbi-c-sign">
            <tbody>
                <tr>
                    <td>{{ __('rbi.form_c.prepared_by') }}</td>
                    <td>{{ __('rbi.form_c.submitted_by') }}</td>
                </tr>
                <tr>
                    <td class="line">Mobarahk Camar</td>
                    <td class="line"></td>
                </tr>
                <tr>
                    <td class="name">{{ __('rbi.form_c.secretary') }}</td>
                    <td class="name">{{ __('rbi.form_c.captain') }}</td>
                </tr>
                <tr>
                    <td class="over">{{ __('rbi.form_c.signature') }}</td>
                    <td class="over">{{ __('rbi.form_c.signature') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-c-date">{{ __('rbi.form_c.date') }}
            <span class="rbi-value">{{ data_get($data, 'date_accomplished') }}</span>
        </p>

        <p class="rbi-c-note">{{ __('rbi.form_c.note') }}</p>
    </div>
@endsection
