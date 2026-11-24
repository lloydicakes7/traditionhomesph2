@extends('pdf.layouts.rbi')

@section('content')
    @php
        /*
         * Form B is placed on fixed coordinates so it matches the original form
         * box for box. Every number is in points, measured from the top-left
         * corner of the A4 sheet.
         *
         * Input boxes: key, x, y, width, height, then the centre x and y of the
         * caption printed under the box. The key is both the data key and the
         * lang key (rbi.form_b.fields.*).
         */
        $fields = [
            ['philsys_no', 60.7, 182.2, 140.2, 19.6, 140.6, 222.4],

            ['last_name', 61.7, 241.1, 140.2, 18.7, 128.5, 271.9],
            ['suffix', 210.3, 241.1, 69.2, 18.7, 248.6, 271.9],
            ['first_name', 286.9, 241.1, 143.9, 18.7, 355.1, 271.9],
            ['middle_name', 440.2, 241.1, 118.7, 18.7, 500.0, 271.9],

            ['birth_date', 62.6, 286.9, 88.8, 20.6, 107.9, 320.5],
            ['birth_place', 156.1, 286.9, 161.7, 20.6, 237.8, 320.5],
            ['sex', 322.4, 286.9, 30.8, 20.6, 342.0, 320.5],
            ['civil_status', 357.9, 286.9, 68.2, 20.6, 393.4, 320.5],
            ['religion', 429.9, 286.9, 129.0, 20.6, 485.0, 320.5],

            ['residence_address', 63.5, 334.6, 347.6, 19.6, 231.8, 361.7],
            ['citizenship', 414.9, 334.6, 143.9, 19.6, 479.9, 361.7],

            ['occupation', 64.5, 381.3, 161.7, 21.5, 145.3, 406.5],
            ['contact_no', 234.6, 381.3, 175.7, 21.5, 323.3, 406.5],
            ['email', 418.7, 381.3, 140.2, 21.5, 489.7, 406.5],
        ];

        // Region, city, province and barangay: lang key, label x, row centre y,
        // colon x, line x, line width, data key.
        $locations = [
            ['region', 37.4, 105.6, 109.3, 123.9, 127.5, 'address.region'],
            ['city_municipality', 286.0, 105.6, 324.3, 412.1, 121.5, 'address.city_municipality'],
            ['province', 37.4, 122.4, 109.3, 123.9, 127.5, 'address.province'],
            ['barangay', 286.0, 122.4, 360.7, 411.2, 122.4, 'address.barangay'],
        ];

        // Highest educational attainment: tick box x and label x, in the order
        // of rbi.form_b.education.levels.
        $levelBoxX = [184.1, 256.1, 333.6, 398.1, 470.1];
        $levelTextX = [197.2, 270.1, 347.6, 414.9, 484.1];

        // Graduate / Under Graduate: data key, tick box x, label x.
        $specify = [
            ['graduate', 287.8, 308.4],
            ['undergraduate', 373.8, 395.3],
        ];

        // left / top / width / height of an absolutely positioned element.
        $place = fn ($x, $y, $w, $h = null) => 'left: '.$x.'pt; top: '.$y.'pt; width: '.$w.'pt;'.($h === null ? '' : ' height: '.$h.'pt;');

        // A value shrinks to fit its box instead of running into the caption.
        $fit = function ($value, $width, $size = 8.0) {
            $length = max(mb_strlen((string) $value), 1);

            return 'font-size: '.min($size, max(5.0, round(($width - 4) / ($length * 0.65), 1))).'pt;';
        };

        // Centres a value inside a box of inner width/height: small line-height
        // plus computed top padding, so DomPDF centres it the same at every font
        // size. $nudge moves the text up (positive) or down (negative).
        $center = function ($value, $w, $h, $size = 8.0, $nudge = 0.4) use ($fit) {
            preg_match('/font-size:\s*([\d.]+)pt/', $fit($value, $w, $size), $m);
            $fontSize = (float) ($m[1] ?? $size);
            $lh = round($fontSize * 1.15, 2);
            $pad = max(0, round(($h - $lh) / 2 - $nudge, 2));

            return 'font-size: '.$fontSize.'pt; line-height: '.$lh.'pt; padding-top: '.$pad.'pt; height: '.max(0, $h - $pad).'pt;';
        };

        // "RBI Form B" is bold, the "(Revised 2024)" after it is not.
        $title = (string) __('rbi.form_b.title');
        $cut = strpos($title, ' (');
        $titleBold = $cut === false ? $title : substr($title, 0, $cut);
        $titleRest = $cut === false ? '' : substr($title, $cut);

        $levels = array_keys((array) __('rbi.form_b.education.levels'));
        $levelLabels = array_values((array) __('rbi.form_b.education.levels'));
    @endphp

    <div class="rbi-page rbi-b">
        <div class="rbi-b-heading" style="{{ $place(44.9, 31.4, 300) }}"><strong>{{ $titleBold }}</strong>{{ $titleRest }}</div>
        <div class="rbi-b-title" style="{{ $place(93, 67.8, 400) }}">{{ __('rbi.form_b.subtitle') }}</div>

        {{-- Region, city / municipality, province, barangay --}}
        @foreach ($locations as $location)
            @php
                [$key, $labelX, $centerY, $colonX, $lineX, $lineWidth, $dataKey] = $location;
                $value = data_get($data, $dataKey);
            @endphp
            <div class="rbi-b-label" style="{{ $place($labelX, $centerY - 5, 70) }}">{{ rtrim((string) __('rbi.form_b.fields.'.$key), ': ') }}</div>
            <div class="rbi-b-label" style="{{ $place($colonX, $centerY - 5, 6) }}">:</div>
            <div class="rbi-b-fill rbi-value" style="{{ $place($lineX, $centerY - 6.7, $lineWidth, 10) }} {{ $fit($value, $lineWidth) }}">{{ $value }}</div>
        @endforeach

        {{-- The bordered form --}}
        <div class="rbi-b-frame" style="{{ $place(22.4, 147.7, 550.2, 651.1) }}"></div>
        <div class="rbi-b-section" style="{{ $place(211.2, 163.1, 200) }}">{{ __('rbi.form_b.personal') }}</div>

        {{-- Personal information: an input box with its caption underneath --}}
        @foreach ($fields as $field)
            @php
                [$key, $x, $y, $w, $h, $captionX, $captionY] = $field;
                $value = data_get($data, $key);
            @endphp
            <div class="rbi-b-box rbi-value" style="{{ $place($x, $y, $w - 1.5) }} {{ $center($value, $w, $h - 1.5) }}">{{ $value }}</div>
            <div class="rbi-b-caption" style="{{ $place($captionX - 70, $captionY - 5, 140) }}">{{ __('rbi.form_b.fields.'.$key) }}</div>
        @endforeach

        {{-- Highest educational attainment --}}
        <div class="rbi-b-label" style="{{ $place(29.9, 430.5, 153) }}">{{ __('rbi.form_b.education.title') }}</div>
        @foreach ($levels as $index => $level)
            @if (isset($levelBoxX[$index]))
                <div class="rbi-b-check" style="{{ $place($levelBoxX[$index], 428, 11.6) }} {{ $center('✓', 11.6, 12.5, 9.0, 0) }}">{{ data_get($data, 'education.level') === $level ? '✓' : '' }}</div>
                <div class="rbi-b-label" style="{{ $place($levelTextX[$index], 430.5, 80) }}">{{ $levelLabels[$index] }}</div>
            @endif
        @endforeach

        <div class="rbi-b-label rbi-b-italic" style="{{ $place(216.8, 450.1, 70) }}">{{ __('rbi.form_b.education.specify') }}</div>
        @foreach ($specify as $item)
            @php
                [$key, $boxX, $textX] = $item;
            @endphp
            <div class="rbi-b-check" style="{{ $place($boxX, 445.8, 16.3) }} {{ $center('✓', 16.3, 14.4, 9.0, 0) }}">{{ data_get($data, 'education.'.$key) ? '✓' : '' }}</div>
            <div class="rbi-b-label" style="{{ $place($textX, 450.1, 80) }}">{{ __('rbi.form_b.education.'.$key) }}</div>
        @endforeach

        {{-- Privacy consent --}}
        <div class="rbi-b-privacy" style="{{ $place(37.4, 479.9, 511.2) }}">{{ __('rbi.form_b.privacy') }}</div>

        {{-- Accomplished by --}}
        <div class="rbi-b-fill rbi-value" style="{{ $place(72.9, 559.3, 134.6, 10) }}">{{ data_get($data, 'date') }}</div>
        <div class="rbi-b-dash" style="{{ $place(324.3, 564.7, 202.8, 0) }}"></div>
        <div class="rbi-b-sign" style="{{ $place(58.5, 571.1, 160) }}">{{ __('rbi.form_b.date_accomplished') }}</div>
        <div class="rbi-b-sign" style="{{ $place(306.1, 571.1, 240) }}">{{ __('rbi.form_b.name_signature') }}</div>

        {{-- Attested by --}}
        <div class="rbi-b-sign rbi-b-left" style="{{ $place(63.5, 620.6, 100) }}">{{ __('rbi.form_b.attested') }}</div>
        <div class="rbi-b-rule" style="{{ $place(63.5, 681.5, 150.5, 0) }}"></div>
        <div class="rbi-b-sign rbi-b-bold" style="{{ $place(65.6, 683.2, 150) }}">{{ __('rbi.form_b.secretary') }}</div>

        <div class="rbi-b-thumb" style="{{ $place(349.5, 613, 74.2, 64.8) }}"></div>
        <div class="rbi-b-thumb" style="{{ $place(446.7, 613, 74.2, 64.8) }}"></div>
        <div class="rbi-b-sign" style="{{ $place(337.4, 683.2, 100) }}">{{ __('rbi.form_b.left_thumbmark') }}</div>
        <div class="rbi-b-sign" style="{{ $place(434.6, 683.2, 100) }}">{{ __('rbi.form_b.right_thumbmark') }}</div>

        {{-- Household number --}}
        <div class="rbi-b-sign rbi-b-left rbi-b-bold" style="{{ $place(63.5, 730, 88) }}">{{ __('rbi.form_b.fields.household_number') }}</div>
        <div class="rbi-b-box rbi-value" style="{{ $place(151.4, 719.6, 112.5) }} {{ $center(data_get($data, 'household_no'), 114, 23.7) }}">{{ data_get($data, 'household_no') }}</div>
        <div class="rbi-b-label rbi-b-italic" style="{{ $place(63.5, 750.1, 400) }}">{{ __('rbi.form_b.note') }}</div>
    </div>
@endsection