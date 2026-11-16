{{--
    MEMBERSHIP FORM, drawn as the association's own full-width form.

    The sheet is the Membership_Form.html design: the letterhead at the top,
    the member details on their printed lines, the household table, and the
    checkers and president at the foot. Every value comes from the member's own
    record through App\Queries\Documents\MembershipFormDocumentQuery; a detail
    the record leaves blank stays a blank line.

    Every blank is an inline-block aligned on the text baseline, with a
    non-breaking space inside so an empty blank keeps its underline.
    Column widths sit on the cells (never table-layout: fixed, which makes the
    PDF renderer split the columns equally).

    Data: App\Queries\Documents\MembershipFormDocumentQuery.
--}}
@php
    $type = strtolower(trim(str_replace(['_', '-'], ' ', (string) ($member_type ?? ''))));
    $isSettler = str_contains($type, 'settler');
    $isCaretaker = str_contains($type, 'caretaker');
    $isTenant = str_contains($type, 'tenant') || str_contains($type, 'rent');
    $isOwner = ! $isSettler && ! $isCaretaker && ! $isTenant
        && ($type === 'owned' || str_contains($type, 'owner'));

    $isMale = strcasecmp((string) ($sex ?? ''), 'Male') === 0;
    $isFemale = strcasecmp((string) ($sex ?? ''), 'Female') === 0;

    $civil = strtolower((string) ($civil_status ?? ''));
    $isSingle = $civil === 'single';
    $isMarried = $civil === 'married';
    $isSeparated = $civil === 'separated';
    $isWidow = in_array($civil, ['widow', 'widowed'], true);

    // Mark inside ( ): plain inline text, so it stays on the line's baseline.
    $mark = fn ($on) => $on ? 'X' : '&#160;';

    $rows = $members ?? [];
    $rowCount = $member_rows ?? 13;
@endphp
<x-doc.paper form="membership-form" :title="__('documents.membership.title')">
    <div class="thha-mf-page">
        <div class="thha-mf-logo-hoa">
            <x-doc.logo :src="$letterhead['left_image'] ?? null" :w="23.88" :h="23.88" />
        </div>
        <div class="thha-mf-logo-hope">
            <x-doc.logo :src="$letterhead['right_image'] ?? null" :w="32.51" :h="16.26" />
        </div>
        <div class="thha-mf-logo-trece">
            <x-doc.logo :src="$letterhead['footer_image'] ?? null" :w="38.61" :h="15.24" />
        </div>

        <header class="thha-mf-header">
            <div class="thha-mf-org">TRADITION HOMES PHASE 2 HOMEOWNERS ASSOCIATION, INC.</div>
            <div class="thha-mf-small">BLOCK 20, LOT 06, PHASE 2, TRADITION HOMES,</div>
            <div class="thha-mf-small">BARANGAY INOCENCIO, TRECE MARTIRES CITY, CAVITE</div>
            <div class="thha-mf-small">HLURB REGISTRATION # 02104</div>
            <div class="thha-mf-small">CONTACT NO. (046) 238-1172 / 0977-749-1246</div>
            <div class="thha-mf-small">Email: traditionhomeshomeownersasso@gmail.com</div>
            <div class="thha-mf-small">Non-VAT Registration TIN#633-841-093-00000</div>
        </header>

        <div class="thha-mf-title">M E M B E R S H I P&nbsp; F O R M</div>

        <div class="thha-mf-date">Date <span class="thha-mf-longline thha-mf-w1">{{ $date['long'] }}&#160;</span></div>

        <div class="thha-mf-status">
            <table>
                <tr>
                    <td><span class="thha-mf-box">{{ $isOwner ? 'X' : '' }}</span>Owner</td>
                    <td><span class="thha-mf-box">{{ $isTenant ? 'X' : '' }}</span>Tenant</td>
                    <td><span class="thha-mf-box">{{ $isCaretaker ? 'X' : '' }}</span>Caretaker</td>
                    <td><span class="thha-mf-box">{{ $isSettler ? 'X' : '' }}</span>Informal<br>Settler</td>
                </tr>
            </table>
        </div>

        <section class="thha-mf-fields">
            <table class="thha-mf-name-row">
                <tr>
                    <td class="thha-mf-first" style="width: 28%">
                        <div class="thha-mf-write">{{ $surname }}</div>
                        <div class="thha-mf-label">Surname</div>
                    </td>
                    <td style="width: 28%">
                        <div class="thha-mf-write">{{ $first_name }}</div>
                        <div class="thha-mf-label">First Name</div>
                    </td>
                    <td style="width: 28%">
                        <div class="thha-mf-write">{{ $middle_name }}</div>
                        <div class="thha-mf-label">Middle Name</div>
                    </td>
                    <td class="thha-mf-last" style="width: 16%">
                        <div class="thha-mf-write">{{ $religion }}</div>
                        <div class="thha-mf-label">Religion</div>
                    </td>
                </tr>
            </table>

            <div class="thha-mf-line">
                Sex&nbsp; ( {!! $mark($isMale) !!} ) Male&nbsp;&nbsp;&nbsp;
                ( {!! $mark($isFemale) !!} ) Female
                <span style="margin-left:0.55in">Citizenship</span>
                <span class="thha-mf-longline thha-mf-w1">{{ $citizenship }}&#160;</span>
                <span style="margin-left:1.45in">Age</span>
                <span class="thha-mf-longline" style="width:0.65in">{{ $age }}&#160;</span>
            </div>

            <div class="thha-mf-line">
                Birth date <span class="thha-mf-longline thha-mf-w1">{{ $birth_date['long'] }}&#160;</span>
                <span style="margin-left:0.65in">Birthplace</span>
                <span class="thha-mf-longline" style="width:2.8in">{{ $birth_place }}&#160;</span>
            </div>

            <div class="thha-mf-line">
                Civil Status: ( {!! $mark($isSingle) !!} ) Single&nbsp;&nbsp;
                ( {!! $mark($isMarried) !!} ) Married&nbsp;&nbsp;
                ( {!! $mark($isSeparated) !!} ) Separated&nbsp;&nbsp;
                ( {!! $mark($isWidow) !!} ) Widow
            </div>

            <div class="thha-mf-line">
                If married, Name of Spouse:
                <span class="thha-mf-longline" style="width:5.15in">{{ $spouse_name }}&#160;</span>
            </div>

            <div class="thha-mf-line">
                Address: Present: &nbsp; Block
                <span class="thha-mf-longline" style="width:0.45in">{{ $block }}&#160;</span>&nbsp; Lot
                <span class="thha-mf-longline" style="width:0.45in">{{ $lot }}&#160;</span>&nbsp;
                Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite
            </div>

            <div class="thha-mf-line">
                Previous: <span class="thha-mf-longline thha-mf-w4">{{ $previous_address }}&#160;</span>
            </div>

            <div class="thha-mf-line">
                Highest Educational Attainment:
                <span class="thha-mf-longline" style="width:4.75in">{{ $education }}&#160;</span>
            </div>

            <div class="thha-mf-line">
                Occupation: <span class="thha-mf-longline thha-mf-w1">{{ $occupation }}&#160;</span>
                <span style="margin-left:0.75in">Mobile No. #:</span>
                <span class="thha-mf-longline thha-mf-w1">{{ $mobile }}&#160;</span>
            </div>
        </section>

        <div class="thha-mf-house-title"><strong>List of Household Members</strong></div>

        <table class="thha-mf-household">
            <thead>
                <tr>
                    <th style="width: 37%">Name</th>
                    <th style="width: 7.5%">Age</th>
                    <th style="width: 10%">B-date</th>
                    <th style="width: 14%">B-place</th>
                    <th style="width: 12%">Relation</th>
                    <th style="width: 19.5%">Occupation</th>
                </tr>
            </thead>
            <tbody>
                @for ($index = 0; $index < $rowCount; $index++)
                    @php($member = $rows[$index] ?? [])
                    <tr>
                        <td>{{ $member['name'] ?? '' }}</td>
                        <td>{{ $member['age'] ?? '' }}</td>
                        <td>{{ $member['birth_date'] ?? '' }}</td>
                        <td>{{ $member['birth_place'] ?? '' }}</td>
                        <td>{{ $member['relation'] ?? '' }}</td>
                        <td>{{ $member['occupation'] ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <table class="thha-mf-owner-sign">
            <tr>
                <td>
                    Name of owner:
                    <span class="thha-mf-owner-line">{{ $owner_name }}&#160;</span>
                </td>
                <td style="width: 2.30in">
                    <div class="thha-mf-sig"></div>
                    <div class="thha-mf-sig-label">Member's Signature</div>
                </td>
            </tr>
        </table>

        <div class="thha-mf-checked">Checked by:</div>

        <table class="thha-mf-approvers">
            <tr>
                <td>
                    <div class="thha-mf-approver">
                        <div class="thha-mf-signline"></div>
                        <strong>{{ $staff['checkers'][0]['name'] ?? '' }}</strong><br>
                        {{ __('documents.membership.chairwoman') }}<br>
                        {{ __('documents.membership.committee') }}
                    </div>
                </td>
                <td>
                    <div class="thha-mf-approver">
                        <div class="thha-mf-signline"></div>
                        <strong>{{ $staff['checkers'][1]['name'] ?? '' }}</strong><br>
                        {{ __('documents.membership.chairwoman') }}<br>
                        {{ __('documents.membership.committee') }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="thha-mf-approved">Approved by:</div>

        <div class="thha-mf-president">
            <div class="thha-mf-signline"></div>
            <strong>{{ $staff['signer']['name'] ?? '' }}</strong><br>
            {{ __('documents.membership.president') }}
        </div>

        <div class="thha-mf-note">
            NOTE:&nbsp;&nbsp;&nbsp; 1. Membership Fee - Php {{ $fee }}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. Monthly Dues - Php {{ $dues }}
        </div>
    </div>
</x-doc.paper>