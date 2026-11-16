{{--
    HOMEOWNERS ASSOCIATION PERMIT, drawn as the association's own A4 form.

    The sheet is the HOA_Permit.html design: the letterhead at the top, the
    officers down the left with the control numbers, and the permit letter at
    the right. The permit is granted to a resident; the purpose and location are
    the request's own where the office printed it from one.

    The frame holds two absolutely positioned blocks (sidebar 50mm, letter 140mm)
    instead of table cells, so the PDF renderer cannot split the columns 50/50.
    Same construct as endorsement-letter.blade.php.

    Data: App\Queries\Documents\HoaPermitDocumentQuery.
--}}
@php
    $address = trim(($address_line_1 ?? '') . ' ' . ($address_line_2 ?? ''));
@endphp
<x-doc.paper form="hoa-permit" :title="__('documents.permit.title')">
    <div class="thha-hp-sheet">
        <header class="thha-hp-header">
            <div class="thha-hp-logo-hoa">
                <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="24" :h="24" />
            </div>
            <div class="thha-hp-logo-hope">
                <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="30" :h="20" />
            </div>

            <div class="thha-hp-org">TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.</div>
            <div class="thha-hp-line">BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,</div>
            <div class="thha-hp-line">BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE</div>
            <div class="thha-hp-line">HLURB REGISTRATION # 02104</div>
            <div class="thha-hp-line">CONTACT NO. (046) 238-1172 / 0977-749-1246</div>
            <div class="thha-hp-email">Email: traditionhomeshomeownersasso@gmail.com</div>
            <div>Non-Vat Registration TIN#633-841-093-00000</div>
        </header>

        <div class="thha-hp-frame">
            {{-- ===== Sidebar ===== --}}
            <div class="thha-hp-sidebar">
                <div class="thha-hp-portrait">
                    <x-doc.logo :src="$staff['photo'] ?? null" :w="31" :h="31" />
                </div>

                @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                    <div class="thha-hp-person">
                        <strong>{{ $staff[$office]['name'] ?? '' }}&#160;</strong>
                        <span class="thha-hp-role">{{ $staff[$office]['role'] ?? '' }}</span>
                    </div>
                @endforeach

                <div class="thha-hp-board-title">{{ __('documents.sidebar.board') }}</div>

                @foreach (($staff['committees'] ?? []) as $committee)
                    <div class="thha-hp-committee">
                        <strong>{{ $committee['name'] }}</strong>{{ $committee['role'] }}
                    </div>
                @endforeach

                <div class="thha-hp-control">
                    <div>Control No. <span class="thha-hp-blank">{{ $control_no }}&#160;</span></div>
                    <div>O.R. No. <span class="thha-hp-blank">{{ $or_no }}&#160;</span></div>
                </div>
            </div>

            {{-- ===== Letter ===== --}}
            <div class="thha-hp-main">
                <div class="thha-hp-title-text">HOMEOWNERS ASSOCIATION PERMIT</div>

                <div class="thha-hp-intro">Permit is hereby granted</div>

                <table class="thha-hp-fields">
                    <tr>
                        <td class="thha-hp-label">to:</td>
                        <td class="thha-hp-value">{{ $granted_to }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label">Address:</td>
                        <td class="thha-hp-value thha-hp-value--wrap">{{ $address }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label">Purpose:</td>
                        <td class="thha-hp-value">{{ $purpose_line_1 }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label"></td>
                        <td class="thha-hp-value thha-hp-value--multi">{{ $purpose_line_2 }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label"></td>
                        <td class="thha-hp-value thha-hp-value--multi"></td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label">Location:</td>
                        <td class="thha-hp-value">{{ $location }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label">Date of event:</td>
                        <td class="thha-hp-value">{{ $event_period }}</td>
                    </tr>
                    <tr>
                        <td class="thha-hp-label">Date Issued:</td>
                        <td class="thha-hp-value">{{ $issued_date['long'] }}</td>
                    </tr>
                </table>

                <div class="thha-hp-explanation">
                    In view of the foregoing, this HOA, through the undersigned, interposes no
                    objection to the issuance of the corresponding permits being applied for.
                </div>

                <div class="thha-hp-note">
                    Note:<br>
                    <strong>Not valid without HOA Dry Seal</strong>
                </div>

                <div class="thha-hp-certified">Certified by:</div>

                <div class="thha-hp-signature">
                    <div class="thha-hp-rule"></div>
                    <strong>{{ $staff['signer']['name'] ?? '' }}</strong>
                    {{ __('documents.permit.role') }}
                </div>

                <div class="thha-hp-logo-bagong">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="48" :h="22" />
                </div>
            </div>
        </div>

        <div class="thha-hp-footer">Non-VAT Registration TIN#633-841-093-00000</div>
    </div>
</x-doc.paper>