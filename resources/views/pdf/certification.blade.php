{{--
    CERTIFICATION, printed as the association's sheet: the letterhead at the top, the
    officers down the left with the control numbers, and the document itself
    at the right. Built the same way as the endorsement letter: the frame holds
    two absolutely positioned blocks (sidebar 49mm, letter 141mm) instead of
    table cells, and every blank is an inline-block with a fixed height so the
    typed value sits on its line.

    Data: App\Queries\Documents\CertificationDocumentQuery.
--}}
@php
    $headerHtml = ($letterhead['header_html'] ?? '') !== ''
        ? $letterhead['header_html']
        : '<div class="thha-cert-header__title">TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.</div>'
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
        if ($purpose['label'] === 'Others') {
            $other = $purpose['value'];
        }
    }

    // The purposes fill the left column first (5), then the right.
    $perColumn = 5;
@endphp
<x-doc.paper form="certification" :title="__('documents.certification.title')">
    <div class="thha-pdf-page">
        <table class="thha-cert-header">
            <colgroup>
                <col style="width: 24mm">
                <col>
                <col style="width: 30mm">
            </colgroup>
            <tr>
                <td class="thha-cert-header__mark">
                    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="$letterhead['left_width'] ?? 24" :h="24" />
                </td>
                <td class="thha-cert-header__text">{!! $headerHtml !!}</td>
                <td class="thha-cert-header__mark thha-cert-header__mark--right">
                    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="$letterhead['right_width'] ?? 24" :h="20" />
                </td>
            </tr>
        </table>

        <div class="thha-cert-frame">
            <div class="thha-cert-sidebar">
                <div class="thha-cert-photo">
                    <x-doc.logo :src="$staff['photo'] ?? null" :w="31.75" :h="31.75" />
                </div>

                @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                    <div class="thha-cert-officer">
                        <span class="thha-cert-officer__name">{{ $staff[$office]['name'] ?? '' }}&#160;</span>
                        <span class="thha-cert-officer__role">{{ $staff[$office]['role'] ?? '' }}</span>
                    </div>
                @endforeach

                <div class="thha-cert-board-heading">{{ __('documents.sidebar.board') }}</div>

                @foreach (($staff['board'] ?? []) as $committee)
                    <div class="thha-cert-officer">
                        <span class="thha-cert-officer__name">{{ $committee['name'] }}</span>
                        <span class="thha-cert-officer__role">{{ $committee['role'] }}</span>
                    </div>
                @endforeach

                <div class="thha-cert-control">
                    <div>Control No. <span class="thha-cert-control__value">{{ $control_no }}</span></div>
                    <div>O.R. No. <span class="thha-cert-control__value">{{ $or_no }}</span></div>
                </div>
            </div>

            {{-- ===== Letter ===== --}}
            <div class="thha-cert-letter">
                <div class="thha-cert-title-text">CERTIFICATION</div>

                <p class="thha-cert-center">This is to certify that Block <span class="thha-cert-blank thha-cert-blank--unit">{{ $block }}&#160;</span> Lot <span class="thha-cert-blank thha-cert-blank--unit">{{ $lot }}&#160;</span> Tradition Homes Phase 2<br>Barangay Inocencio, Trece Martires City, Cavite is currently occupied by</p>

                <div class="thha-cert-occupant">{{ $salutation !== '' ? $salutation : 'MR./MS./MRS.' }} <span class="thha-cert-blank thha-cert-blank--name thha-cert-blank--left">{{ $occupant_name }}&#160;</span></div>

                <p class="thha-cert-paragraph">He/She is known to be of good moral character, a law-abiding citizen, and has no derogatory records in this subdivision.</p>
                <p class="thha-cert-paragraph">This certification is being issued upon the request of the above-named for whatever legal purposes it may serve him/her best.</p>

                <div class="thha-cert-purpose-title">PURPOSE:</div>

                <table class="thha-cert-options thha-cert-options-table">
                    @for ($row = 0; $row < $perColumn; $row++)
                        <tr>
                            @foreach ([$row, $row + $perColumn] as $index)
                                @php($purpose = $purposes[$index] ?? null)
                                <td>
                                    @if ($purpose)
                                        <span class="thha-cert-check">{!! $purpose['checked'] ? 'X' : '&#160;' !!}</span>{{ $purpose['label'] }}@if ($purpose['label'] === 'Others' && $other !== ''){{ ' — ' . $other }}@endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </table>

                <div class="thha-cert-given">Given this <span class="thha-cert-blank thha-cert-blank--day">{{ $issued_date['day'] }}&#160;</span> day of <span class="thha-cert-blank thha-cert-blank--month">{{ $issued_date['month'] }}&#160;</span> <span class="thha-cert-blank thha-cert-blank--year">{{ $issued_date['year'] }}&#160;</span> at Tradition Homes Homeowners Association Office.</div>

                <div class="thha-cert-note">
                    Note:
                    <ol>
                        <li>Certification is valid for three (3) months from the date issued.</li>
                        <li>Not valid without HOA Dry Seal.</li>
                    </ol>
                </div>

                <div class="thha-cert-conforme">Conforme:</div>

                <table class="thha-cert-signatories">
                    <tr>
                        <td>
                            <strong>{{ $staff['signatories']['treasurer'] ?? '' }}</strong><br>
                            {{ __('documents.certification.treasurer_role') }}
                        </td>
                        <td>
                            <strong>{{ $staff['signatories']['president'] ?? '' }}</strong><br>
                            {{ __('documents.certification.president_role') }}
                        </td>
                    </tr>
                </table>

                <div class="thha-cert-brand">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="42" :h="11" />
                </div>
            </div>
        </div>

        <div class="thha-cert-footer">{!! $footerText !!}</div>
    </div>
</x-doc.paper>