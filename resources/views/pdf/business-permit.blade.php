{{--
    BUSINESS PERMIT, printed as the association's sheet: the letterhead at the top, the
    officers down the left with the control numbers, and the document itself
    at the right. Built the same way as the endorsement letter: the frame holds
    two absolutely positioned blocks (sidebar 49mm, letter 141mm) instead of
    table cells, and every blank is an inline-block with a fixed height so the
    typed value sits on its line.

    Data: App\Queries\Documents\BusinessPermitDocumentQuery.
--}}
@php
    $headerHtml = ($letterhead['header_html'] ?? '') !== ''
        ? $letterhead['header_html']
        : '<div class="thha-bp-header__title">TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.</div>'
            . '<div><strong>BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,</strong></div>'
            . '<div><strong>BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE</strong></div>'
            . '<div><strong>HLURB REGISTRATION # 02104</strong></div>'
            . '<div><strong>CONTACT NO. (046) 238-1172 / 0977-749-1246</strong></div>'
            . '<div>Email: traditionhomeshomeownersasso@gmail.com</div>'
            . '<div>Non-Vat Registration TIN#633-841-093-00000</div>';

    $footerText = ($letterhead['footer_text'] ?? '') !== ''
        ? $letterhead['footer_text']
        : 'Non-VAT Registration TIN#633-841-093-00000';
@endphp
@php
    $other = '';

    foreach ($purposes as $purpose) {
        if ($purpose['label'] === 'Other') {
            $other = $purpose['value'];
        }
    }

    // Two columns, left to right: the first three down the left, the last three beside them.
    $optionLabels = [];

    foreach ($purposes as $purpose) {
        $optionLabels[$purpose['label']] = $purpose['label'] . ($purpose['label'] === 'Other'
            ? ($other !== '' ? ': ' . $other : ': _______________')
            : '');
    }
@endphp
<x-doc.paper form="business-permit" :title="__('documents.business_permit.title')">
    <div class="thha-pdf-page">
        <table class="thha-bp-header">
            <colgroup>
                <col style="width: 24mm">
                <col>
                <col style="width: 30mm">
            </colgroup>
            <tr>
                <td class="thha-bp-header__mark">
                    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="$letterhead['left_width'] ?? 24" :h="24" />
                </td>
                <td class="thha-bp-header__text">{!! $headerHtml !!}</td>
                <td class="thha-bp-header__mark thha-bp-header__mark--right">
                    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="$letterhead['right_width'] ?? 24" :h="20" />
                </td>
            </tr>
        </table>

        <div class="thha-bp-frame">
            <div class="thha-bp-sidebar">
                <div class="thha-bp-photo">
                    <x-doc.logo :src="$staff['photo'] ?? null" :w="31.75" :h="31.75" />
                </div>

                @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                    <div class="thha-bp-officer">
                        <span class="thha-bp-officer__name">{{ $staff[$office]['name'] ?? '' }}&#160;</span>
                        <span class="thha-bp-officer__role">{{ $staff[$office]['role'] ?? '' }}</span>
                    </div>
                @endforeach

                <div class="thha-bp-board-heading">{{ __('documents.sidebar.board') }}</div>

                @foreach (($staff['committees'] ?? []) as $committee)
                    <div class="thha-bp-officer">
                        <span class="thha-bp-officer__name">{{ $committee['name'] }}</span>
                        <span class="thha-bp-officer__role">{{ $committee['role'] }}</span>
                    </div>
                @endforeach

                <div class="thha-bp-control">
                    <div>Control No. <span class="thha-bp-control__value">{{ $control_no }}</span></div>
                    <div>O.R. No. <span class="thha-bp-control__value">{{ $or_no }}</span></div>
                </div>
            </div>

            {{-- ===== Letter ===== --}}
            <div class="thha-bp-letter">
                <div class="thha-bp-title-text">BUSINESS PERMIT</div>

                <p class="thha-bp-field">Name of Applicant: <span class="thha-bp-blank thha-bp-blank--name">{{ $applicant_name }}&#160;</span>&nbsp;&nbsp; Gender: <span class="thha-bp-blank thha-bp-blank--gender">{{ $gender }}&#160;</span></p>
                <p class="thha-bp-field">Address: Block <span class="thha-bp-blank thha-bp-blank--unit">{{ $block }}&#160;</span> Lot <span class="thha-bp-blank thha-bp-blank--unit">{{ $lot }}&#160;</span> Phase 2 Tradition Homes, Barangay Inocencio,<br>Trece Martires City, Cavite</p>
                <p class="thha-bp-field thha-bp-field--details">Date of Birth: <span class="thha-bp-blank thha-bp-blank--birth">{{ $birth_date['long'] }}&#160;</span>&nbsp;&nbsp; Age: <span class="thha-bp-blank thha-bp-blank--age">{{ $age }}&#160;</span>&nbsp; Mobile No.: <span class="thha-bp-blank thha-bp-blank--mobile">{{ $mobile_no }}&#160;</span></p>

                <div class="thha-bp-purpose">
                    <div class="thha-bp-purpose-title">PURPOSE:</div>

                    <table class="thha-bp-options">
                        @for ($row = 0; $row < 3; $row++)
                            <tr>
                                @foreach ([$row, $row + 3] as $index)
                                    @php($option = $purposes[$index] ?? null)
                                    <td>
                                        @if ($option)
                                            <span class="thha-bp-check">{!! $option['checked'] ? 'X' : '&#160;' !!}</span>{{ $optionLabels[$option['label']] ?? $option['label'] }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                    </table>
                </div>

                <div class="thha-bp-certification">
                    This is to certify that the above – written name is a resident of Phase 2 Tradition Homes,
                    Barangay Inocencio, Trece Martires City, Cavite. This certifies further that he/she is of
                    good moral character and has no violation of the THHA rules and regulations.
                    <br>
                    This certification is being issued this, <span class="thha-bp-blank thha-bp-blank--day">{{ $issued_date['day'] }}&#160;</span> day of <span class="thha-bp-blank thha-bp-blank--month">{{ $issued_date['month'] }}&#160;</span> <span class="thha-bp-blank thha-bp-blank--year">{{ $issued_date['year'] }}&#160;</span> for such legal purpose it may serve him/her best.
                </div>

                <div class="thha-bp-note">
                    Note:
                    <ol>
                        <li>Certification is valid for three (3) months from the date issued.</li>
                        <li>Not valid without HOA Dry Seal.</li>
                    </ol>
                </div>

                <div class="thha-bp-conforme">Conforme:</div>

                <table class="thha-bp-signatories">
                    <tr>
                        <td>
                            <strong>{{ $staff['treasurer']['name'] ?? '' }}</strong><br>
                            {{ __('documents.business_permit.treasurer_role') }}
                        </td>
                        <td>
                            <strong>{{ $staff['president']['name'] ?? '' }}</strong><br>
                            {{ __('documents.business_permit.president_role') }}
                        </td>
                    </tr>
                </table>

                <div class="thha-bp-brand">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="42" :h="11" />
                </div>
            </div>
        </div>

        <div class="thha-bp-footer">{!! $footerText !!}</div>
    </div>
</x-doc.paper>