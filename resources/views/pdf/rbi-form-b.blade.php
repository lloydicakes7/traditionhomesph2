@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-b">
        <p class="rbi-b-title">{{ __('rbi.form_b.title') }}</p>
        <p class="rbi-b-subtitle">{{ __('rbi.form_b.subtitle') }}</p>

        <table class="rbi-b-loc">
            <tbody>
                <tr>
                    <td>{{ __('rbi.form_b.fields.region') }}</td>
                    <td class="rbi-b-fill">{{ data_get($data, 'address.region') }}</td>
                    <td>{{ __('rbi.form_b.fields.city_municipality') }}</td>
                    <td class="rbi-b-fill">{{ data_get($data, 'address.city_municipality') }}</td>
                </tr>
                <tr>
                    <td>{{ __('rbi.form_b.fields.province') }}</td>
                    <td class="rbi-b-fill">{{ data_get($data, 'address.province') }}</td>
                    <td>{{ __('rbi.form_b.fields.barangay') }}</td>
                    <td class="rbi-b-fill">{{ data_get($data, 'address.barangay') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-b-section">{{ __('rbi.form_b.personal') }}</p>

        <table class="rbi-b-name">
            <tbody>
                <tr>
                    <td colspan="4" class="field">{{ data_get($data, 'philsys_no') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="caption">{{ __('rbi.form_b.fields.philsys_no') }}</td>
                </tr>
                <tr>
                    <td style="width: 38%;" class="field">{{ data_get($data, 'last_name') }}</td>
                    <td style="width: 13%;" class="field">{{ data_get($data, 'suffix') }}</td>
                    <td style="width: 28%;" class="field">{{ data_get($data, 'first_name') }}</td>
                    <td style="width: 21%;" class="field">{{ data_get($data, 'middle_name') }}</td>
                </tr>
                <tr>
                    <td class="caption">{{ __('rbi.form_b.fields.last_name') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.suffix') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.first_name') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.middle_name') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="rbi-b-details">
            <tbody>
                <tr>
                    <td style="width: 16%;" class="field">{{ data_get($data, 'birth_date') }}</td>
                    <td style="width: 25%;" class="field">{{ data_get($data, 'birth_place') }}</td>
                    <td style="width: 8%;" class="field">{{ data_get($data, 'sex') }}</td>
                    <td style="width: 16%;" class="field">{{ data_get($data, 'civil_status') }}</td>
                    <td style="width: 35%;" class="field">{{ data_get($data, 'religion') }}</td>
                </tr>
                <tr>
                    <td class="caption">{{ __('rbi.form_b.fields.birth_date') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.birth_place') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.sex') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.civil_status') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.religion') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="rbi-b-address">
            <tbody>
                <tr>
                    <td style="width: 75%;">{{ data_get($data, 'residence_address') }}<br>
                        <span class="caption">{{ __('rbi.form_b.fields.residence_address') }}</span>
                    </td>
                    <td style="width: 25%;">{{ data_get($data, 'citizenship') }}<br>
                        <span class="caption">{{ __('rbi.form_b.fields.citizenship') }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="rbi-b-contact">
            <tbody>
                <tr>
                    <td style="width: 40%;" class="field">{{ data_get($data, 'occupation') }}</td>
                    <td style="width: 30%;" class="field">{{ data_get($data, 'contact_no') }}</td>
                    <td style="width: 30%;" class="field">{{ data_get($data, 'email') }}</td>
                </tr>
                <tr>
                    <td class="caption">{{ __('rbi.form_b.fields.occupation') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.contact_no') }}</td>
                    <td class="caption">{{ __('rbi.form_b.fields.email') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="rbi-b-education">
            <div class="rbi-b-education-line">
                {{ __('rbi.form_b.education.title') }}
                @foreach (__('rbi.form_b.education.levels') as $level => $label)
                    <span class="rbi-box">{{ data_get($data, 'education.level') === $level ? '✓' : '' }}</span>{{ $label }}
                @endforeach
            </div>
            <div class="rbi-b-specify">
                {{ __('rbi.form_b.education.specify') }}
                <span class="rbi-box">{{ data_get($data, 'education.graduate') ? '✓' : '' }}</span>{{ __('rbi.form_b.education.graduate') }}
                <span class="rbi-box">{{ data_get($data, 'education.undergraduate') ? '✓' : '' }}</span>{{ __('rbi.form_b.education.undergraduate') }}
            </div>
        </div>

        <p class="rbi-b-privacy">{{ __('rbi.form_b.privacy') }}</p>

        <table class="rbi-b-accomplished">
            <tbody>
                <tr>
                    <td class="line">{{ data_get($data, 'date') }}</td>
                    <td class="line"></td>
                </tr>
                <tr>
                    <td>{{ __('rbi.form_b.date_accomplished') }}</td>
                    <td>{{ __('rbi.form_b.name_signature') }}</td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-b-attested">{{ __('rbi.form_b.attested') }}</p>

        <table class="rbi-b-attested-line">
            <tbody>
                <tr>
                    <td style="width: 52%;"></td>
                    <td style="width: 24%;" class="rbi-b-thumb">{{ __('rbi.form_b.left_thumbmark') }}</td>
                    <td style="width: 24%;" class="rbi-b-thumb">{{ __('rbi.form_b.right_thumbmark') }}</td>
                </tr>
                <tr>
                    <td class="line"></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="left">{{ __('rbi.form_b.secretary') }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <p class="rbi-b-household">
            {{ __('rbi.form_b.fields.household_number') }}
            <span class="rbi-value">{{ data_get($data, 'household_no') }}</span>
        </p>

        <p class="rbi-b-note">{{ __('rbi.form_b.note') }}</p>
    </div>
@endsection
