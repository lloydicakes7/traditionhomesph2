{{--
    SINUMPAANG SALAYSAY, drawn as the association's own A4 form.

    The sheet is the Sinumpaang_Salaysay.html design: the letterhead and case
    number at the top, the statement on its printed lines, the affiant's
    signature at the right, and the grievance chair and president at the foot.
    The narrative is the affiant's own words, wrapped onto the ruled lines.

    Every blank is an inline-block aligned on the text baseline, with a
    non-breaking space inside so an empty blank keeps its underline.

    Data: App\Queries\Documents\SinumpaangSalaysayDocumentQuery.
--}}
@php
    $narrativeLines = array_slice($narrative ?? [], 0, $narrative_rows ?? 13);
    $dayPartLabel = ($incident_day_part ?? '') !== ''
        ? $incident_day_part
        : 'umaga / tanghali / hapon / gabi';

    // Two-digit year for the "20__" blank: 2026 -> 26.
    $shortYear = fn ($date) => substr((string) ($date['year'] ?? $date['yearShort'] ?? ''), -2);
@endphp
<x-doc.paper form="sinumpaang-salaysay" :title="__('documents.salaysay.title')">
    <div class="thha-ss-page">
        <header class="thha-ss-header">
            <div class="thha-ss-logo-left">
                <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="20" :h="19" />
            </div>

            <div class="thha-ss-org">
                TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.<br>
                BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,<br>
                BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE<br>
                HLURB REGISTRATION # 02104<br>
                CONTACT NO. (046) 238-1172 / 0977-749-1246<br>
                <span class="thha-ss-normal">Email: traditionhomeshomeownersasso@gmail.com</span><br>
                <span class="thha-ss-normal">Non-Vat Registration TIN#633-841-093-00000</span>
            </div>

            <div class="thha-ss-logo-right">
                <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="24" :h="16" />
            </div>
        </header>

        <hr class="thha-ss-rule">

        <div class="thha-ss-case">
            <table>
                <tr>
                    <td class="thha-ss-case-label" style="width: 15mm">Case #:</td>
                    <td class="thha-ss-case-value">{{ $case_no }}</td>
                </tr>
                <tr>
                    <td class="thha-ss-case-label" style="width: 15mm">Case:</td>
                    <td class="thha-ss-case-value">{{ $case_title }}</td>
                </tr>
            </table>
        </div>

        <div class="thha-ss-title">SINUMPAANG SALAYSAY</div>

        <section class="thha-ss-content">
            <p>
                <strong>AKO SI</strong>
                <span class="thha-ss-fill thha-ss-long">{{ $affiant_name }}&#160;</span>,
                <span class="thha-ss-fill thha-ss-short">{{ $affiant_age }}&#160;</span> taong gulang,<br>
                {{ $affiant_civil_status !== '' ? str_replace('-', ' ', $affiant_civil_status) : 'may asawa / binata / dalaga / biyuda' }},
                at kasalukuyang naninirahan sa:
            </p>

            <div class="thha-ss-address-line">{{ $affiant_address }}</div>

            <p>Matapos akong manumpa alinsunod sa batas, buong katapatan kong isinasalaysay ang mga sumusunod:</p>

            <p>
                <strong>1.</strong> Na inirereklamo ko si
                <span class="thha-ss-fill thha-ss-long">{{ $respondent_name }}&#160;</span>,
                <span class="thha-ss-fill thha-ss-short">{{ $respondent_age }}&#160;</span> taong gulang,
                na naninirahan sa:
            </p>

            <div class="thha-ss-section-line">{{ $respondent_address }}</div>

            <p>
                <strong>2.</strong> Na noong ika-
                <span class="thha-ss-fill thha-ss-short">{{ $incident_date['day'] }}&#160;</span>
                ng <span class="thha-ss-fill thha-ss-medium">{{ $incident_month }}&#160;</span>,
                20<span class="thha-ss-fill thha-ss-short">{{ $shortYear($incident_date) }}&#160;</span>,
                bandang alas-<span class="thha-ss-fill thha-ss-short">{{ $incident_time }}&#160;</span>
                ng {{ $dayPartLabel }},<br>
                naganap ang mga sumusunod na pangyayari:
            </p>

            <div class="thha-ss-narrative">
                @for ($index = 0; $index < ($narrative_rows ?? 13); $index++)
                    <div>{{ $narrativeLines[$index] ?? '' }}</div>
                @endfor
            </div>

            <p>
                <strong>3.</strong> Na ginawa ko ang salaysay na ito nang malaya at kusang-loob,
                batay sa aking nalalaman, at walang sinumang pumilit o nanakot sa akin upang gawin ito.
            </p>

            <p>
                <strong>4.</strong> Bilang patunay sa katotohanan ng mga nakasaad dito,
                nilagdaan ko ang salaysay na ito ngayong:<br>
                ika-<span class="thha-ss-fill thha-ss-short">{{ $execution_date['day'] }}&#160;</span>
                ng <span class="thha-ss-fill thha-ss-medium">{{ $execution_month }}&#160;</span>,
                20<span class="thha-ss-fill thha-ss-short">{{ $shortYear($execution_date) }}&#160;</span>.
            </p>

            <div class="thha-ss-signature">
                <div class="thha-ss-sig-line"></div>
                (Lagda ng nagsasalaysay)
            </div>

            <p class="thha-ss-sworn">
                <strong>SINUMPAAN AT NILAGDAAN</strong> sa harap ko ngayong ika-
                <span class="thha-ss-fill thha-ss-medium">{{ $execution_date['day'] }}&#160;</span>
                ng <span class="thha-ss-fill thha-ss-medium">{{ $execution_month }}&#160;</span>,
                20<span class="thha-ss-fill thha-ss-short">{{ $shortYear($execution_date) }}&#160;</span>,
                sa tanggapan ng Tradition Homes Phase 2 Homeowners Association, Inc.,
                Tradition Homes, Barangay Inocencio, Lungsod ng Trece Martires, Cavite.
            </p>
        </section>

        <table class="thha-ss-signatories">
            <tr>
                <td>
                    <div class="thha-ss-signatory">
                        <div class="thha-ss-sig-line"></div>
                        <strong>{{ $staff['grievance']['name'] ?? '' }}</strong><br>
                        {{ __('documents.salaysay.grievance_1') }}<br>
                        {{ __('documents.salaysay.grievance_2') }}
                    </div>
                </td>
                <td>
                    <div class="thha-ss-signatory">
                        <div class="thha-ss-sig-line"></div>
                        <strong>{{ $staff['signatories']['president'] ?? '' }}</strong><br>
                        {{ __('documents.salaysay.president_role') }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="thha-ss-logo-footer">
            <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="50" :h="19" />
        </div>
    </div>
</x-doc.paper>