{{--
    BUSINESS PERMIT, printed on the association's own paper form: the template
    art carries the title, the labels and the blank lines; this view writes the
    applicant's details, the ticked purpose and the signatories onto it.

    Data: App\Queries\Documents\BusinessPermitDocumentQuery.
--}}
@php
    $boxes = [
        'Business Permit'       => 'thha-f-purpose-business-permit',
        'Franchising'           => 'thha-f-purpose-franchising',
        'Internet Connection'   => 'thha-f-purpose-internet',
        'Electrical Connection' => 'thha-f-purpose-electrical',
        'Water Connection'      => 'thha-f-purpose-water',
        'Other'                 => 'thha-f-purpose-other',
    ];

    $checked = [];
    $other = '';

    foreach ($purposes as $purpose) {
        $checked[$purpose['label']] = $purpose['checked'];

        if ($purpose['label'] === 'Other') {
            $other = $purpose['value'];
        }
    }
@endphp
<x-doc.paper form="business-permit" :title="__('documents.business_permit.title')">
    <x-doc.sheet form="business-permit" :letterhead="$letterhead" :staff="$staff">
        <div class="thha-pdf-field thha-f-control-no">{{ $control_no }}</div>
        <div class="thha-pdf-field thha-f-applicant-name">{{ $applicant_name }}</div>
        <div class="thha-pdf-field thha-f-gender">{{ $gender }}</div>
        <div class="thha-pdf-field thha-f-block">{{ $block }}</div>
        <div class="thha-pdf-field thha-f-lot">{{ $lot }}</div>
        <div class="thha-pdf-field thha-f-birth-date">{{ $birth_date['long'] }}</div>
        <div class="thha-pdf-field thha-f-age">{{ $age }}</div>
        <div class="thha-pdf-field thha-f-mobile-no">{{ $mobile_no }}</div>

        @foreach ($boxes as $label => $class)
            @if (! empty($checked[$label]))
                <div class="thha-pdf-field {{ $class }}">X</div>
            @endif
        @endforeach

        @if ($other !== '')
            <div class="thha-pdf-field thha-f-purpose-other-text">{{ $other }}</div>
        @endif

        <div class="thha-pdf-field thha-f-day">{{ $issued_date['day'] }}</div>
        <div class="thha-pdf-field thha-f-month">{{ $issued_date['month'] }}</div>
        <div class="thha-pdf-field thha-f-year">{{ $issued_date['yearShort'] }}</div>

        <div class="thha-d-left-name">{{ $staff['treasurer']['name'] }}</div>
        <div class="thha-d-left-title">{{ __('documents.business_permit.treasurer_role') }}</div>
        <div class="thha-d-right-name">{{ $staff['president']['name'] }}</div>
        <div class="thha-d-right-title">{{ __('documents.business_permit.president_role') }}</div>
    </x-doc.sheet>
</x-doc.paper>
