{{--
    SINUMPAANG SALAYSAY, printed on the association's own paper form: the
    template art carries the Filipino wording and the ruled lines; this view
    writes the case, the two parties, when the incident happened, the narrative
    and who swore it.

    Data: App\Queries\Documents\SinumpaangSalaysayDocumentQuery.
--}}
@php
    $caseTitle = \App\Utility\DocumentText::wrap($case_title, 20, 2);
    $respondentAddress = \App\Utility\DocumentText::wrap($respondent_address, 64, 2);
@endphp
<x-doc.paper form="sinumpaang-salaysay" :title="__('documents.salaysay.title')">
    <x-doc.sheet form="sinumpaang-salaysay" :letterhead="$letterhead" :staff="$staff" :sidebar="false">
        <div class="thha-pdf-field thha-f-case-no">{{ $case_no }}</div>
        <div class="thha-pdf-field thha-f-case-title-1">{{ $caseTitle[0] ?? '' }}</div>
        <div class="thha-pdf-field thha-f-case-title-2">{{ $caseTitle[1] ?? '' }}</div>

        <div class="thha-pdf-field thha-f-affiant-surname">{{ $affiant_name }}</div>
        <div class="thha-pdf-field thha-f-affiant-address">{{ $affiant_address }}</div>

        <div class="thha-pdf-field thha-f-respondent-surname">{{ $respondent_name }}</div>
        <div class="thha-pdf-field thha-f-respondent-address-1">{{ $respondentAddress[0] ?? '' }}</div>
        <div class="thha-pdf-field thha-f-respondent-address-2">{{ $respondentAddress[1] ?? '' }}</div>

        <div class="thha-pdf-field thha-f-incident-day">{{ $incident_date['day'] }}</div>
        <div class="thha-pdf-field thha-f-incident-month">{{ $incident_month }}</div>
        <div class="thha-pdf-field thha-f-incident-year">{{ $incident_date['yearShort'] }}</div>
        <div class="thha-pdf-field thha-f-incident-time">{{ $incident_time }}</div>

        @foreach (array_slice($narrative, 0, 13) as $index => $line)
            <div class="thha-pdf-field thha-f-statement-{{ $index + 1 }}">{{ $line }}</div>
        @endforeach

        <div class="thha-pdf-field thha-f-sign-day">{{ $execution_phrase }}</div>
        <div class="thha-pdf-field thha-f-sworn-day">{{ $execution_date['day'] }}</div>
        <div class="thha-pdf-field thha-f-sworn-month">{{ $execution_month }}</div>
        <div class="thha-pdf-field thha-f-sworn-year">{{ $execution_date['yearShort'] }}</div>

        <div class="thha-d-left-name">{{ $staff['grievance']['name'] }}</div>
        <div class="thha-d-left-title-1">{{ __('documents.salaysay.grievance_1') }}</div>
        <div class="thha-d-left-title-2">{{ __('documents.salaysay.grievance_2') }}</div>
        <div class="thha-d-right-name">{{ $staff['president']['name'] }}</div>
        <div class="thha-d-right-title">{{ __('documents.salaysay.president_role') }}</div>
    </x-doc.sheet>
</x-doc.paper>
