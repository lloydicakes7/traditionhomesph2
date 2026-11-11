{{--
    MEMBERSHIP FORM, printed on the association's own paper form: the template
    art carries the title, the labels and the household table; this view writes
    the member, how the unit is held, the household list and the checkers.

    Data: App\Queries\Documents\MembershipFormDocumentQuery.
--}}
@php
    $memberTypes = [
        'Owner'            => 'thha-f-type-owner',
        'Renter'           => 'thha-f-type-tenant',
        'Caretaker'        => 'thha-f-type-caretaker',
        'Informal Settler' => 'thha-f-type-informal-settler',
    ];

    $sexMarks = [
        'Male'   => 'thha-f-sex-male',
        'Female' => 'thha-f-sex-female',
    ];

    $civilMarks = [
        'Single'    => 'thha-f-civil-single',
        'Married'   => 'thha-f-civil-married',
        'Separated' => 'thha-f-civil-separated',
        'Widow'     => 'thha-f-civil-widow',
    ];

    $columns = [
        'thha-hh-c1' => 'name',
        'thha-hh-c2' => 'age',
        'thha-hh-c3' => 'birth_date',
        'thha-hh-c4' => 'birth_place',
        'thha-hh-c5' => 'relation',
        'thha-hh-c6' => 'occupation',
    ];
@endphp
<x-doc.paper form="membership-form" :title="__('documents.membership.title')">
    <x-doc.sheet form="membership-form" :letterhead="$letterhead" :staff="$staff" :sidebar="false">
        <div class="thha-pdf-field thha-f-date">{{ $date['long'] }}</div>

        @foreach ($memberTypes as $type => $class)
            @if (strcasecmp($member_type, $type) === 0)
                <div class="thha-pdf-field {{ $class }}">X</div>
            @endif
        @endforeach

        <div class="thha-pdf-field thha-f-surname">{{ $surname }}</div>
        <div class="thha-pdf-field thha-f-first-name">{{ $first_name }}</div>
        <div class="thha-pdf-field thha-f-middle-name">{{ $middle_name }}</div>
        <div class="thha-pdf-field thha-f-religion">{{ $religion }}</div>

        @foreach ($sexMarks as $value => $class)
            @if (strcasecmp($sex, $value) === 0)
                <div class="thha-pdf-field {{ $class }}">X</div>
            @endif
        @endforeach

        <div class="thha-pdf-field thha-f-citizenship">{{ $citizenship }}</div>
        <div class="thha-pdf-field thha-f-age">{{ $age }}</div>
        <div class="thha-pdf-field thha-f-birth-date">{{ $birth_date['long'] }}</div>
        <div class="thha-pdf-field thha-f-birth-place">{{ $birth_place }}</div>

        @foreach ($civilMarks as $value => $class)
            @if (strcasecmp($civil_status, $value) === 0)
                <div class="thha-pdf-field {{ $class }}">X</div>
            @endif
        @endforeach

        <div class="thha-pdf-field thha-f-spouse-name">{{ $spouse_name }}</div>
        <div class="thha-pdf-field thha-f-block">{{ $block }}</div>
        <div class="thha-pdf-field thha-f-lot">{{ $lot }}</div>
        <div class="thha-pdf-field thha-f-previous-address">{{ $previous_address }}</div>
        <div class="thha-pdf-field thha-f-education">{{ $education }}</div>
        <div class="thha-pdf-field thha-f-occupation">{{ $occupation }}</div>
        <div class="thha-pdf-field thha-f-mobile-no">{{ $mobile }}</div>

        @foreach (array_slice($members, 0, 13) as $index => $member)
            @foreach ($columns as $class => $key)
                <div class="thha-pdf-cell {{ $class }} thha-hh-r{{ $index + 1 }}">{{ $member[$key] }}</div>
            @endforeach
        @endforeach

        <div class="thha-pdf-field thha-f-owner-name">{{ $owner_name }}</div>

        <div class="thha-d-checker-1-name">{{ $staff['checkers'][0]['name'] }}</div>
        <div class="thha-d-checker-1-position">{{ __('documents.membership.chairwoman') }}</div>
        <div class="thha-d-checker-1-committee">{{ __('documents.membership.committee') }}</div>
        <div class="thha-d-checker-2-name">{{ $staff['checkers'][1]['name'] }}</div>
        <div class="thha-d-checker-2-position">{{ __('documents.membership.chairwoman') }}</div>
        <div class="thha-d-checker-2-committee">{{ __('documents.membership.committee') }}</div>
        <div class="thha-d-approver-name">{{ $staff['signer']['name'] }}</div>
        <div class="thha-d-approver-title">{{ __('documents.membership.president') }}</div>

        <div class="thha-d-membership-fee">- Php {{ $fee }}</div>
        <div class="thha-d-monthly-dues">- Php {{ $dues }}</div>
    </x-doc.sheet>
</x-doc.paper>
