@extends('pdf.layouts.rbi')

@section('content')
    <div class="rbi-page rbi-b">
        <p class="rbi-b-heading">{{ __('rbi.form_b.title') }}</p>
        <p class="rbi-b-title">{{ __('rbi.form_b.subtitle') }}</p>

        <table class="rbi-b-loc">
            <tbody>
                <tr>
                    <td>{{ __('rbi.form_b.fields.region') }}:
                        <span class="rbi-b-line">{{ data_get($data, 'address.region') }}</span>
                    </td>
                    <td>{{ __('rbi.form_b.fields.city_municipality') }}
                        <span class="rbi-b-line">{{ data_get($data, 'address.city_municipality') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('rbi.form_b.fields.province') }}:
                        <span class="rbi-b-line">{{ data_get($data, 'address.province') }}</span>
                    </td>
                    <td>{{ __('rbi.form_b.fields.barangay') }}
                        <span class="rbi-b-line">{{ data_get($data, 'address.barangay') }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="rbi-b-rule"></div>

        <p class="rbi-b-section">{{ __('rbi.form_b.personal') }}</p>

        <table class="rbi-b-table">
            <colgroup>
                @for ($column = 0; $column < 12; $column++)
                    <col style="width: 8.333%;">
                @endfor
            </colgroup>
            <tbody>
                <tr>
                    <td colspan="12" class="rbi-b-philsys">
                        {{ __('rbi.form_b.fields.philsys_no') }}:
                        <span class="rbi-b-line">{{ data_get($data, 'philsys_no') }}</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="12" class="rbi-b-gap"></td>
                </tr>

                {{-- Name --}}
                <tr>
                    <td colspan="3" class="lbl">{{ __('rbi.form_b.fields.last_name') }}</td>
                    <td colspan="2" class="lbl">{{ __('rbi.form_b.fields.suffix') }}</td>
                    <td colspan="3" class="lbl">{{ __('rbi.form_b.fields.first_name') }}</td>
                    <td colspan="4" class="lbl">{{ __('rbi.form_b.fields.middle_name') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="val">{{ data_get($data, 'last_name') }}</td>
                    <td colspan="2" class="val">{{ data_get($data, 'suffix') }}</td>
                    <td colspan="3" class="val">{{ data_get($data, 'first_name') }}</td>
                    <td colspan="4" class="val">{{ data_get($data, 'middle_name') }}</td>
                </tr>

                {{-- Birth and status --}}
                <tr>
                    <td colspan="3" class="lbl">{{ __('rbi.form_b.fields.birth_date') }}</td>
                    <td colspan="4" class="lbl">{{ __('rbi.form_b.fields.birth_place') }}</td>
                    <td class="lbl">{{ __('rbi.form_b.fields.sex') }}</td>
                    <td colspan="2" class="lbl">{{ __('rbi.form_b.fields.civil_status') }}</td>
                    <td colspan="2" class="lbl">{{ __('rbi.form_b.fields.religion') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="val">{{ data_get($data, 'birth_date') }}</td>
                    <td colspan="4" class="val">{{ data_get($data, 'birth_place') }}</td>
                    <td class="val">{{ data_get($data, 'sex') }}</td>
                    <td colspan="2" class="val">{{ data_get($data, 'civil_status') }}</td>
                    <td colspan="2" class="val">{{ data_get($data, 'religion') }}</td>
                </tr>

                {{-- Residence --}}
                <tr>
                    <td colspan="8" class="lbl">{{ __('rbi.form_b.fields.residence_address') }}</td>
                    <td colspan="4" class="lbl">{{ __('rbi.form_b.fields.citizenship') }}</td>
                </tr>
                <tr>
                    <td colspan="8" class="val">{{ data_get($data, 'residence_address') }}</td>
                    <td colspan="4" class="val">{{ data_get($data, 'citizenship') }}</td>
                </tr>

                {{-- Work and contact --}}
                <tr>
                    <td colspan="5" class="lbl">{{ __('rbi.form_b.fields.occupation') }}</td>
                    <td colspan="3" class="lbl">{{ __('rbi.form_b.fields.contact_no') }}</td>
                    <td colspan="4" class="lbl">{{ __('rbi.form_b.fields.email') }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="val">{{ data_get($data, 'occupation') }}</td>
                    <td colspan="3" class="val">{{ data_get($data, 'contact_no') }}</td>
                    <td colspan="4" class="val">{{ data_get($data, 'email') }}</td>
                </tr>

                {{-- Education --}}
                <tr>
                    <td colspan="12" class="rbi-b-education">{{ __('rbi.form_b.education.title') }}</td>
                </tr>
                <tr>
                    <td colspan="12" class="rbi-b-checks">
                        @foreach (__('rbi.form_b.education.levels') as $level => $label)
                            <span class="rbi-box">{{ data_get($data, 'education.level') === $level ? '✓' : '' }}</span>{{ $label }}@if (! $loop->last)&nbsp;&nbsp;&nbsp;@endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td colspan="12" class="rbi-b-checks rbi-b-specify">
                        {{ __('rbi.form_b.education.specify') }}
                        <span class="rbi-box">{{ data_get($data, 'education.graduate') ? '✓' : '' }}</span>{{ __('rbi.form_b.education.graduate') }}
                        &nbsp;&nbsp;
                        <span class="rbi-box">{{ data_get($data, 'education.undergraduate') ? '✓' : '' }}</span>{{ __('rbi.form_b.education.undergraduate') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="12" class="rbi-b-edu-rule"></td>
                </tr>

                {{-- Privacy consent --}}
                <tr>
                    <td colspan="12" class="rbi-b-privacy">{{ __('rbi.form_b.privacy') }}</td>
                </tr>

                {{-- Accomplished by --}}
                <tr>
                    <td colspan="5" class="rbi-b-signline">{{ data_get($data, 'date') }}</td>
                    <td colspan="2"></td>
                    <td colspan="5" class="rbi-b-signline"></td>
                </tr>
                <tr>
                    <td colspan="5" class="rbi-b-caption">{{ __('rbi.form_b.date_accomplished') }}</td>
                    <td colspan="2"></td>
                    <td colspan="5" class="rbi-b-caption">{{ __('rbi.form_b.name_signature') }}</td>
                </tr>

                {{-- Attested by --}}
                <tr>
                    <td colspan="12" class="rbi-b-attested">{{ __('rbi.form_b.attested') }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="rbi-b-signline"></td>
                    <td colspan="3" class="rbi-b-signline"></td>
                    <td colspan="4" class="rbi-b-signline"></td>
                </tr>
                <tr>
                    <td colspan="5" class="rbi-b-caption">{{ __('rbi.form_b.secretary') }}</td>
                    <td colspan="3" class="rbi-b-caption">{{ __('rbi.form_b.left_thumbmark') }}</td>
                    <td colspan="4" class="rbi-b-caption">{{ __('rbi.form_b.right_thumbmark') }}</td>
                </tr>

                {{-- Household number --}}
                <tr>
                    <td colspan="4" class="rbi-b-household-label">{{ __('rbi.form_b.fields.household_number') }}</td>
                    <td colspan="4" class="rbi-b-signline">{{ data_get($data, 'household_no') }}</td>
                    <td colspan="4"></td>
                </tr>

                <tr>
                    <td colspan="12" class="rbi-b-note">{{ __('rbi.form_b.note') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
