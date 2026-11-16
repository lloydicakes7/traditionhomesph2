{{--
    INVITATION LETTER, printed as the association's sheet: the letterhead at the top, the
    officers down the left with the control numbers, and the document itself
    at the right. Built the same way as the endorsement letter: the frame holds
    two absolutely positioned blocks (sidebar 49mm, letter 141mm) instead of
    table cells, and every blank is an inline-block with a fixed height so the
    typed value sits on its line.

    Data: App\Queries\Documents\InvitationLetterDocumentQuery.
--}}
@php
    $headerHtml = ($letterhead['header_html'] ?? '') !== ''
        ? $letterhead['header_html']
        : '<div class="thha-inv-header__title">TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.</div>'
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
<x-doc.paper form="invitation-letter" :title="__('documents.invitation.title')">
    @foreach ($recipients as $index => $recipient)
        <div class="thha-pdf-page" @if ($index > 0) style="page-break-before: always;" @endif>
        <table class="thha-inv-header">
            <colgroup>
                <col style="width: 24mm">
                <col>
                <col style="width: 30mm">
            </colgroup>
            <tr>
                <td class="thha-inv-header__mark">
                    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="$letterhead['left_width'] ?? 24" :h="24" />
                </td>
                <td class="thha-inv-header__text">{!! $headerHtml !!}</td>
                <td class="thha-inv-header__mark thha-inv-header__mark--right">
                    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="$letterhead['right_width'] ?? 24" :h="20" />
                </td>
            </tr>
        </table>

        <div class="thha-inv-frame">
            <div class="thha-inv-sidebar">
                <div class="thha-inv-photo">
                    <x-doc.logo :src="$staff['photo'] ?? null" :w="31.75" :h="31.75" />
                </div>

                @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                    <div class="thha-inv-officer">
                        <span class="thha-inv-officer__name">{{ $staff[$office]['name'] ?? '' }}&#160;</span>
                        <span class="thha-inv-officer__role">{{ $staff[$office]['role'] ?? '' }}</span>
                    </div>
                @endforeach

                <div class="thha-inv-board-heading">{{ __('documents.sidebar.board') }}</div>

                @foreach (($staff['committees'] ?? []) as $committee)
                    <div class="thha-inv-officer">
                        <span class="thha-inv-officer__name">{{ $committee['name'] }}</span>
                        <span class="thha-inv-officer__role">{{ $committee['role'] }}</span>
                    </div>
                @endforeach

                <div class="thha-inv-control">
                    <div>Control No. <span class="thha-inv-control__value">{{ $control_no }}</span></div>
                    <div>O.R. No. <span class="thha-inv-control__value">{{ $or_no }}</span></div>
                </div>
            </div>

            {{-- ===== Letter ===== --}}
            <div class="thha-inv-letter">
                <div class="thha-inv-title-text">INVITATION LETTER</div>

                <div class="thha-inv-row thha-inv-row--date">Date: <span class="thha-inv-blank thha-inv-blank--date">{{ $date['long'] }}&#160;</span></div>

                <div class="thha-inv-row">To: <span class="thha-inv-blank thha-inv-blank--to thha-inv-blank--left">{{ $recipient['name'] }}&#160;</span></div>
                <div class="thha-inv-row">Address: <span class="thha-inv-blank thha-inv-blank--address thha-inv-blank--left">{{ $recipient['address_1'] }}&#160;</span></div>
                <div class="thha-inv-row thha-inv-address2"><span class="thha-inv-blank thha-inv-blank--address2 thha-inv-blank--left">{{ $recipient['address_2'] }}&#160;</span></div>

                <div class="thha-inv-subject">Subject: Invitation to Attend a Meeting</div>
                <div class="thha-inv-salutation">Dear <span class="thha-inv-blank thha-inv-blank--name thha-inv-blank--left">{{ $recipient['first_name'] }}&#160;</span>:</div>

                <p class="thha-inv-paragraph">You are respectfully invited to attend a meeting at the Tradition Homes Phase 2 Homeowners Association Office, located at Block 20, Lot 06, Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite.</p>

                <p class="thha-inv-paragraph">The meeting is scheduled for <span class="thha-inv-blank thha-inv-blank--meeting">{{ $meeting_date['long'] }}&#160;</span> at exactly <span class="thha-inv-blank thha-inv-blank--time">{{ $meeting_time }}&#160;</span> ({{ $day_part !== '' ? $day_part : 'morning/afternoon/evening' }}).</p>

                <p class="thha-inv-paragraph">This meeting will provide an opportunity to discuss a matter that requires attention, clarify relevant information, and consider appropriate steps toward its resolution. Your presence and cooperation will be greatly appreciated.</p>

                <div class="thha-inv-closing">Respectfully yours,</div>

                <div class="thha-inv-signature">
                    <div class="thha-inv-signature__line"></div>
                    <div class="thha-inv-signature__name">
                        @if (! empty($staff['signer']['name']))
                            Mr. {{ $staff['signer']['name'] }}
                        @endif
                    </div>
                    <div>{{ __('documents.invitation.role') }}</div>
                </div>

                <div class="thha-inv-received-block">
                    <div class="thha-inv-row">{{ __('documents.invitation.received') }} <span class="thha-inv-blank thha-inv-blank--received">&#160;</span></div>
                    <div class="thha-inv-row">{{ __('documents.invitation.date_time') }} <span class="thha-inv-blank thha-inv-blank--datetime">&#160;</span></div>
                    <div class="thha-inv-row">{{ __('documents.invitation.given') }} <span class="thha-inv-blank thha-inv-blank--given">&#160;</span></div>
                </div>

                <div class="thha-inv-brand">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="42" :h="11" />
                </div>
            </div>
        </div>

        <div class="thha-inv-footer">{!! $footerText !!}</div>
        </div>
    @endforeach
</x-doc.paper>