@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-c">
        <p class="rbi-c-title">{{ __('rbi.form_c.title') }}</p>
        <p class="rbi-c-subtitle">{{ __('rbi.form_c.subtitle') }}</p>
        <p class="rbi-c-term">{{ __('rbi.form_c.term', ['semester' => data_get($data, 'semester'), 'year' => data_get($data, 'year')]) }}</p>

        <table class="rbi-c-meta">
            <tbody>
                @foreach (['region', 'province', 'city_municipality', 'barangay', 'total_inhabitants', 'total_households', 'total_families'] as $field)
                    <tr>
                        <td class="label">{{ __('rbi.form_c.fields.' . $field) }}</td>
                        <td class="fill">{{ data_get($data, $field) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="rbi-c-table">
            <thead>
                <tr>
                    <th>{{ __('rbi.form_c.columns.indicator') }}</th>
                    <th>{{ __('rbi.form_c.columns.male') }}</th>
                    <th>{{ __('rbi.form_c.columns.female') }}</th>
                    <th>{{ __('rbi.form_c.columns.total') }}</th>
                    <th class="rbi-c-remarks">{{ __('rbi.form_c.columns.remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="rbi-c-section">
                    <td colspan="5">{{ __('rbi.form_c.age_title') }}</td>
                </tr>
                @foreach (__('rbi.form_c.age_brackets') as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'age_brackets.' . $key . '.male') }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'age_brackets.' . $key . '.female') }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'age_brackets.' . $key . '.total') }}</td>
                        <td>{{ data_get($data, 'age_brackets.' . $key . '.remarks') }}</td>
                    </tr>
                @endforeach

                <tr class="rbi-c-section">
                    <td colspan="5">{{ __('rbi.form_c.sector_title') }}</td>
                </tr>
                @foreach (__('rbi.form_c.sectors') as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'sectors.' . $key . '.male') }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'sectors.' . $key . '.female') }}</td>
                        <td class="rbi-c-number">{{ data_get($data, 'sectors.' . $key . '.total') }}</td>
                        <td>{{ data_get($data, 'sectors.' . $key . '.remarks') }}</td>
                    </tr>
                @endforeach

                @foreach (['civil_status', 'citizenship'] as $group)
                    @foreach (__('rbi.form_c.' . $group) as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="rbi-c-number">{{ data_get($data, $group . '.' . $key . '.male') }}</td>
                            <td class="rbi-c-number">{{ data_get($data, $group . '.' . $key . '.female') }}</td>
                            <td class="rbi-c-number">{{ data_get($data, $group . '.' . $key . '.total') }}</td>
                            <td>{{ data_get($data, $group . '.' . $key . '.remarks') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <table class="rbi-c-sign">
            <tbody>
                <tr>
                    <td class="label">{{ __('rbi.form_c.prepared_by') }}</td>
                    <td class="label">{{ __('rbi.form_c.submitted_by') }}</td>
                </tr>
                <tr>
                    <td class="line"></td>
                    <td class="line"></td>
                </tr>
                <tr>
                    <td class="name">{{ __('rbi.form_c.secretary') }}</td>
                    <td class="name">{{ __('rbi.form_c.captain') }}</td>
                </tr>
                <tr>
                    <td>{{ __('rbi.form_c.signature') }}</td>
                    <td>{{ __('rbi.form_c.signature') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-c-date">{{ __('rbi.form_c.date') }} <span class="rbi-value">{{ data_get($data, 'date_accomplished') }}</span></p>

        <p class="rbi-c-note">{{ __('rbi.form_c.note') }}</p>
    </div>
@endsection
