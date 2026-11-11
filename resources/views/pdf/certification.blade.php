{{--
    CERTIFICATION, printed on the association's own paper form: the template
    art carries the wording, the purpose boxes and the signature lines; this
    view writes the unit, the occupant, the ticked purpose and the signs.

    Data: App\Queries\Documents\CertificationDocumentQuery.
--}}
@php
    $boxes = [
        'Residency'             => 'thha-f-purpose-residency',
        'Employment'            => 'thha-f-purpose-employment',
        'Burial Assistance'     => 'thha-f-purpose-burial',
        'Indigency'             => 'thha-f-purpose-indigency',
        'Others'                => 'thha-f-purpose-others',
        'Financial Assistance'  => 'thha-f-purpose-financial',
        'Educational Assistance' => 'thha-f-purpose-educational',
        'Loan'                  => 'thha-f-purpose-loan',
        'Moved out'             => 'thha-f-purpose-moved-out',
    ];

    $checked = [];

    foreach ($purposes as $purpose) {
        $checked[$purpose['label']] = $purpose['checked'];
    }
@endphp
<x-doc.paper form="certification" :title="__('documents.certification.title')">
    <x-doc.sheet form="certification" :letterhead="$letterhead" :staff="$staff">
        <div class="thha-pdf-field thha-f-control-no">{{ $control_no }}</div>
        <div class="thha-pdf-field thha-f-or-no">{{ $or_no }}</div>
        <div class="thha-pdf-field thha-f-block">{{ $block }}</div>
        <div class="thha-pdf-field thha-f-lot">{{ $lot }}</div>
        <div class="thha-pdf-field thha-f-occupant-name">{{ $occupant_name }}</div>

        @foreach ($boxes as $label => $class)
            @if (! empty($checked[$label]))
                <div class="thha-pdf-field {{ $class }}">X</div>
            @endif
        @endforeach

        <div class="thha-pdf-field thha-f-day">{{ $issued_date['day'] }}</div>
        <div class="thha-pdf-field thha-f-month">{{ $issued_date['month'] }}</div>
        <div class="thha-pdf-field thha-f-year">{{ $issued_date['yearShort'] }}</div>

        <div class="thha-d-left-name">{{ $staff['treasurer']['name'] }}</div>
        <div class="thha-d-left-title">{{ __('documents.certification.treasurer_role') }}</div>
        <div class="thha-d-right-name">{{ $staff['president']['name'] }}</div>
        <div class="thha-d-right-title">{{ __('documents.certification.president_role') }}</div>
    </x-doc.sheet>
</x-doc.paper>
