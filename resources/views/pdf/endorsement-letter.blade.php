{{--
    ENDORSEMENT LETTER, printed as the association's letter: the letterhead at
    the top, the officers down the left with the control numbers, and the letter
    itself at the right. The complainant, the party complained of and their
    units are written on the letter's own blanks; the complaint on its ruled
    lines.

    The frame holds two absolutely positioned blocks (sidebar 49mm, letter 141mm)
    instead of table cells, so the PDF renderer cannot split the columns 50/50.

    Every blank is an inline-block aligned on the text baseline, with a
    non-breaking space inside so empty blanks keep the same baseline.

    Data: App\Queries\Documents\EndorsementLetterDocumentQuery.
--}}
@php
    $recipient = $staff['recipient'] ?? [];

    $addressee = [
        $addressee_name !== '' ? $addressee_name : ($recipient['name'] ?? ''),
        $addressee_position !== '' ? $addressee_position : ($recipient['position'] ?? ''),
        $addressee_office !== '' ? $addressee_office : ($recipient['office'] ?? ''),
        $addressee_city !== '' ? $addressee_city : ($recipient['city'] ?? ''),
    ];

    $concernLines = [$concern_line_1, $concern_line_2, $concern_line_3];
@endphp
<x-doc.paper form="endorsement-letter" :title="__('documents.endorsement.title')">
    <div class="thha-pdf-page">
        <table class="thha-e-header">
            <colgroup>
                <col style="width: 24mm">
                <col>
                <col style="width: 30mm">
            </colgroup>
            <tr>
                <td class="thha-e-header__mark">
                    <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="$letterhead['left_width'] ?? 24" :h="24" />
                </td>
                <td class="thha-e-header__text">{!! $letterhead['header_html'] ?? '' !!}</td>
                <td class="thha-e-header__mark thha-e-header__mark--right">
                    <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="$letterhead['right_width'] ?? 24" :h="20" />
                </td>
            </tr>
        </table>

        <div class="thha-e-frame">
            <div class="thha-e-sidebar">
                <div class="thha-e-photo">
                    <x-doc.logo :src="$staff['photo'] ?? null" :w="31.75" :h="31.75" />
                </div>

                @foreach (['president', 'vice', 'secretary', 'treasurer', 'auditor'] as $office)
                    <div class="thha-e-officer">
                        <span class="thha-e-officer__name">{{ $staff[$office]['name'] ?? '' }}&#160;</span>
                        <span class="thha-e-officer__role">{{ $staff[$office]['role'] ?? '' }}</span>
                    </div>
                @endforeach

                <div class="thha-e-board-heading">{{ __('documents.sidebar.board') }}</div>

                @foreach (($staff['board'] ?? []) as $committee)
                    <div class="thha-e-officer">
                        <span class="thha-e-officer__name">{{ $committee['name'] }}</span>
                        <span class="thha-e-officer__role">{{ $committee['role'] }}</span>
                    </div>
                @endforeach

                <div class="thha-e-control">
                    <div>{{ __('documents.endorsement.control') }} <span class="thha-e-control__value">{{ $control_no }}</span></div>
                    <div>{{ __('documents.endorsement.or_no') }} <span class="thha-e-control__value">{{ $or_no }}</span></div>
                </div>
            </div>

            {{-- ===== Letter ===== --}}
            <div class="thha-e-letter">
                <div class="thha-e-title-text">{{ __('documents.endorsement.title') }}</div>

                <div class="thha-e-date">
                    {{ __('documents.endorsement.date') }}
                    <span class="doc-line thha-e-blank thha-e-blank--date">{{ $date['long'] }}&#160;</span>
                </div>

                <div class="thha-e-recipient">
                    @foreach ($addressee as $line)
                        <div>{{ $line }}&nbsp;</div>
                    @endforeach
                </div>

                <div class="thha-e-subject">{{ __('documents.endorsement.subject') }}</div>
                <div class="thha-e-salutation">{{ __('documents.endorsement.salutation') }}</div>

                <p class="thha-e-paragraph">
                    {{ __('documents.endorsement.intro_a') }}
                    <span class="doc-line thha-e-blank thha-e-blank--name">{{ $complainant_name }}&#160;</span>{{ __('documents.endorsement.intro_b') }}
                    <span class="doc-line thha-e-blank thha-e-blank--block">{{ $complainant_block }}&#160;</span>{{ __('documents.endorsement.intro_c') }}
                    <span class="doc-line thha-e-blank thha-e-blank--block">{{ $complainant_lot }}&#160;</span>{{ __('documents.endorsement.intro_d') }}
                    <span class="doc-line thha-e-blank thha-e-blank--name">{{ $respondent_name }}&#160;</span>{{ __('documents.endorsement.intro_e') }}
                    <span class="doc-line thha-e-blank thha-e-blank--block">{{ $respondent_block }}&#160;</span>{{ __('documents.endorsement.intro_f') }}
                    <span class="doc-line thha-e-blank thha-e-blank--block">{{ $respondent_lot }}&#160;</span>{{ __('documents.endorsement.intro_g') }}
                </p>

                <div class="thha-e-complaint">{{ __('documents.endorsement.problem') }}</div>

                <div class="thha-e-lines">
                    @foreach ($concernLines as $line)
                        <div class="thha-e-line">{{ $line }}</div>
                    @endforeach
                </div>

                <p class="thha-e-paragraph">{{ __('documents.endorsement.attempt') }}</p>
                <p class="thha-e-paragraph">{{ __('documents.endorsement.referral') }}</p>
                <p class="thha-e-paragraph">{{ __('documents.endorsement.thanks') }}</p>

                <div class="thha-e-closing">{{ __('documents.endorsement.respectfully') }}</div>

                <div class="thha-e-signature">
                    <div class="thha-e-signature__line"></div>
                    <div class="thha-e-signature__name">
                        @if (! empty($staff['signer']['name']))
                            {{ __('documents.endorsement.signer_prefix') }} {{ $staff['signer']['name'] }}
                        @endif
                    </div>
                    <div>{{ __('documents.endorsement.role') }}</div>
                </div>

                <div class="thha-e-brand">
                    <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="42" :h="11" />
                </div>
            </div>
        </div>

        <div class="thha-e-footer">{!! $letterhead['footer_text'] ?? '' !!}</div>
    </div>
</x-doc.paper>