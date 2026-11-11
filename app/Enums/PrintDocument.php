<?php

declare(strict_types=1);

namespace App\Enums;

use App\Utility\MembershipFormDetails;

/**
 * The association's printable documents — the fixed list the Print Documents
 * menu, the preview pages and the PDF route all read from.
 *
 * A document is one of the association's forms or letters, so the set is
 * business-fixed and lives in code rather than in a table: the sidebar offers
 * these twelve and nothing else, each slug resolves to the `pdf.print.*` view
 * that draws it, and each document states the validation its own parameters
 * carry. A view never names a document of its own — it reads the enum.
 *
 * @method static PrintDocument MEMBERSHIP_FORM()
 * @method static PrintDocument RESIDENTS_LIST()
 * @method static PrintDocument BUSINESS_PERMIT()
 * @method static PrintDocument HOA_PERMIT()
 * @method static PrintDocument ENDORSEMENT_LETTER()
 * @method static PrintDocument CERTIFICATION()
 * @method static PrintDocument INVITATION_LETTER()
 * @method static PrintDocument SINUMPAANG_SALAYSAY()
 * @method static PrintDocument JOURNAL_VOUCHER()
 * @method static PrintDocument VIOLATION_NOTICE()
 * @method static PrintDocument FINANCIAL_REPORT()
 * @method static PrintDocument SUMMARY_REPORT()
 * @method static PrintDocument OCCUPANCY_LIST()
 */
class PrintDocument extends Enum
{
    const MEMBERSHIP_FORM      = 'membership-form';
    const RESIDENTS_LIST       = 'residents-list';
    const BUSINESS_PERMIT      = 'business-permit';
    const HOA_PERMIT           = 'hoa-permit';
    const ENDORSEMENT_LETTER   = 'endorsement-letter';
    const CERTIFICATION        = 'certification';
    const INVITATION_LETTER    = 'invitation-letter';
    const SINUMPAANG_SALAYSAY  = 'sinumpaang-salaysay';
    const JOURNAL_VOUCHER      = 'journal-voucher';
    const VIOLATION_NOTICE     = 'violation-notice';
    const FINANCIAL_REPORT     = 'financial-report';
    const SUMMARY_REPORT       = 'summary-report';
    const OCCUPANCY_LIST       = 'occupancy-list';

    /**
     * The document as the menu writes it.
     */
    public function label(): string
    {
        return [
            self::MEMBERSHIP_FORM     => 'Membership Form',
            self::RESIDENTS_LIST      => 'Residents List',
            self::BUSINESS_PERMIT     => 'Business Permit',
            self::HOA_PERMIT          => 'HOA Permit',
            self::ENDORSEMENT_LETTER  => 'Endorsement Letter',
            self::CERTIFICATION       => 'Certification',
            self::INVITATION_LETTER   => 'Invitation Letter',
            self::SINUMPAANG_SALAYSAY => 'Sinumpaang Salaysay',
            self::JOURNAL_VOUCHER     => 'Journal Voucher',
            self::VIOLATION_NOTICE    => 'Violation Notice',
            self::FINANCIAL_REPORT    => 'Financial Report',
            self::SUMMARY_REPORT      => 'Summary Report',
            self::OCCUPANCY_LIST      => 'Occupancy List',
        ][$this->value] ?? $this->value;
    }

    /**
     * The Font Awesome icon the menu and the page header use.
     */
    public function icon(): string
    {
        return [
            self::MEMBERSHIP_FORM     => 'fas fa-fw fa-user-check',
            self::RESIDENTS_LIST      => 'fas fa-fw fa-list-ol',
            self::BUSINESS_PERMIT     => 'fas fa-fw fa-store',
            self::HOA_PERMIT          => 'fas fa-fw fa-hard-hat',
            self::ENDORSEMENT_LETTER  => 'fas fa-fw fa-envelope-open-text',
            self::CERTIFICATION       => 'fas fa-fw fa-certificate',
            self::INVITATION_LETTER   => 'fas fa-fw fa-envelope',
            self::SINUMPAANG_SALAYSAY => 'fas fa-fw fa-file-signature',
            self::JOURNAL_VOUCHER     => 'fas fa-fw fa-file-invoice-dollar',
            self::VIOLATION_NOTICE    => 'fas fa-fw fa-exclamation-triangle',
            self::FINANCIAL_REPORT    => 'fas fa-fw fa-coins',
            self::SUMMARY_REPORT      => 'fas fa-fw fa-table',
            self::OCCUPANCY_LIST      => 'fas fa-fw fa-users',
        ][$this->value] ?? 'fas fa-fw fa-file-pdf';
    }

    /**
     * The URL slug the route and the Livewire component are addressed by.
     */
    public function slug(): string
    {
        return $this->value;
    }

    /**
     * The `pdf.` view that draws the document.
     *
     * One view per document, named after the slug: `pdf.journal-voucher`,
     * `pdf.journal-voucher`, and so on. Every printable document lives here,
     * because the paper is the same whether it is opened from the Print
     * Documents page or from the record it belongs to.
     */
    public function view(): string
    {
        return 'pdf.' . $this->value;
    }

    /**
     * The paper size this document is designed for.
     *
     * The association's own paper (a4, letter or legal) is a print setting and
     * overrides this; a document that must be a particular size states it here.
     */
    public function paper(): string
    {
        return 'a4';
    }

    /**
     * The orientation the document is laid out in — a document that has to fit
     * a table across the sheet runs across an A4 sheet, every other document
     * down it.
     *
     * The residents list is the association's whole register of units, and the
     * summary report is a year of months beside its accounts: thirteen money
     * columns and an account do not fit a sheet held the tall way, so both are
     * laid out the wide way.
     */
    public function orientation(): string
    {
        return in_array($this->value, [self::RESIDENTS_LIST, self::SUMMARY_REPORT], true)
            ? 'landscape'
            : 'portrait';
    }

    /**
     * Whether the office may print this document with nothing filled in.
     *
     * The paper forms handed out at the desk: the membership form, the permits,
     * the certificate, the statement and the letters. A financial report or a
     * voucher has no blank paper, so it is never offered one.
     */
    public function hasBlankForm(): bool
    {
        return in_array($this->value, [
            self::MEMBERSHIP_FORM,
            self::HOA_PERMIT,
            self::ENDORSEMENT_LETTER,
            self::INVITATION_LETTER,
            self::CERTIFICATION,
            self::SINUMPAANG_SALAYSAY,
            self::BUSINESS_PERMIT,
        ], true);
    }

    /**
     * The page's width-to-height ratio, as CSS `aspect-ratio` takes it.
     *
     * The preview panel sizes its viewer from this, so the sheet keeps its
     * shape at every screen width instead of being squeezed into a fixed
     * height.
     */
    public function paperRatio(): string
    {
        if ($this->orientation() === 'landscape') {
            return '297 / 210';
        }

        return $this->hasBlankForm() ? '215.9 / 330.2' : '210 / 297';
    }

    /**
     * The validation the document's own parameters carry.
     *
     * The one place a document's parameters are declared: the preview page's
     * form offers exactly these fields, and its builder validates exactly these
     * rules before it draws anything. The paper forms the office hands out —
     * the membership form, the permits, the certificate, the letters and the
     * statement — print with whatever they were given: every detail the office
     * has not supplied stays a blank line instead of stopping the print.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        switch ($this->value) {
            // The membership form is filled in from the resident's own record
            // — who holds the unit, where they lived before and the unit's
            // owner are read from the household, never retyped here.
            case self::MEMBERSHIP_FORM:
                $rules = [
                    'residentId'       => ['nullable', 'integer', 'exists:residents,id'],
                    'dateAccomplished' => ['nullable', 'date'],
                    // The request a form is printed from: the details supplied
                    // for it are read from the record, never from the URL.
                    'requestId'        => ['nullable', 'integer', 'exists:requests,id'],
                ];

                // A detail the record leaves blank may be supplied on the print
                // page, so the office can complete the sheet before printing it.
                foreach (MembershipFormDetails::printable() as $key => $label) {
                    $rules[$key] = ['nullable', 'string', 'max:' . MembershipFormDetails::FIELDS[$key]['max']];
                }

                return $rules;

            // One address at a time: a list of every unit on file is too large
            // a sheet to draw, so the address decides which properties are
            // listed (and the builder's own row cap keeps the sheet printable).
            // The two numbers are the "No." column's own span, so one address
            // can be printed in batches; neither is required, and a lone end
            // number is a span from the first unit.
            case self::RESIDENTS_LIST:
                return [
                    'addressId' => ['required', 'integer', 'exists:addresses,id'],
                    'block'     => ['nullable', 'string', 'max:50'],
                    'sortBy'    => ['nullable', 'in:block,name,type'],
                    'startNo'   => ['nullable', 'integer', 'min:1'],
                    'endNo'     => ['nullable', 'integer', 'min:1'],
                ];

            case self::BUSINESS_PERMIT:
                return [
                    'residentId'    => ['nullable', 'integer', 'exists:residents,id'],
                    'purpose'       => ['nullable', 'in:' . implode(',', BusinessPermitPurpose::values())],
                    'purposeOther'  => ['nullable', 'string', 'max:80', 'required_if:purpose,' . BusinessPermitPurpose::OTHER],
                    'controlNumber' => ['nullable', 'string', 'max:30'],
                    'issuedAt'      => ['nullable', 'date'],
                ];

            case self::HOA_PERMIT:
                return [
                    'residentId'    => ['nullable', 'integer', 'exists:residents,id'],
                    'purpose'       => ['nullable', 'string', 'max:160'],
                    'contractor'    => ['nullable', 'string', 'max:120'],
                    'workers'       => ['nullable', 'integer', 'min:1', 'required_with:contractor'],
                    'location'      => ['nullable', 'string', 'max:200'],
                    'eventFrom'     => ['nullable', 'date'],
                    'eventTo'       => ['nullable', 'date', 'after_or_equal:eventFrom'],
                    'controlNumber' => ['nullable', 'string', 'max:30'],
                    'orNumber'      => ['nullable', 'string', 'max:30'],
                    'issuedAt'      => ['nullable', 'date'],
                ];

            case self::ENDORSEMENT_LETTER:
                return [
                    'residentId'         => ['nullable', 'integer', 'exists:residents,id'],
                    'respondentId'       => ['nullable', 'integer', 'exists:residents,id', 'different:residentId'],
                    'respondentBlock'    => ['nullable', 'string', 'max:20'],
                    'respondentLot'      => ['nullable', 'string', 'max:20'],
                    'addresseeName'      => ['nullable', 'string', 'max:120'],
                    'addresseePosition'  => ['nullable', 'string', 'max:120'],
                    'addresseeOffice'    => ['nullable', 'string', 'max:120'],
                    'addresseeCity'      => ['nullable', 'string', 'max:120'],
                    'problem'            => ['nullable', 'string', 'max:500'],
                    'mediationAttempted' => ['boolean'],
                    'controlNumber'      => ['nullable', 'string', 'max:30'],
                    'orNumber'           => ['nullable', 'string', 'max:30'],
                    'letterDate'         => ['nullable', 'date'],
                ];

            case self::CERTIFICATION:
                return [
                    'residentId'    => ['nullable', 'integer', 'exists:residents,id'],
                    'purpose'       => ['nullable', 'in:' . implode(',', CertificationPurpose::values())],
                    'purposeOther'  => ['nullable', 'string', 'max:80', 'required_if:purpose,' . CertificationPurpose::OTHERS],
                    'controlNumber' => ['nullable', 'string', 'max:30'],
                    'orNumber'      => ['nullable', 'string', 'max:30'],
                    'issuedAt'      => ['nullable', 'date'],
                ];

            case self::INVITATION_LETTER:
                return [
                    'residentId'    => ['nullable', 'integer', 'exists:residents,id'],
                    'allHomeowners' => ['boolean'],
                    'eventTitle'    => ['nullable', 'string', 'max:80'],
                    'concern'       => ['nullable', 'string', 'max:300'],
                    'venue'         => ['nullable', 'string', 'max:200'],
                    'meetingDate'   => ['nullable', 'date'],
                    'meetingTime'   => ['nullable', 'date_format:H:i'],
                    'letterDate'    => ['nullable', 'date'],
                ];

            case self::SINUMPAANG_SALAYSAY:
                return [
                    'residentId'    => ['nullable', 'integer', 'exists:residents,id'],
                    'respondentId'  => ['nullable', 'integer', 'exists:residents,id', 'different:residentId'],
                    'caseNumber'    => ['nullable', 'string', 'max:40'],
                    'caseTitle'     => ['nullable', 'string', 'max:120'],
                    'incidentDate'  => ['nullable', 'date'],
                    'incidentTime'  => ['nullable', 'date_format:H:i'],
                    'narrative'     => ['nullable', 'string', 'max:2000'],
                    'executionDate' => ['nullable', 'date'],
                ];

            case self::JOURNAL_VOUCHER:
                return [
                    'journalEntryId' => ['required', 'integer', 'exists:journal_entries,id'],
                ];

            case self::VIOLATION_NOTICE:
                return [
                    'violationId' => ['required', 'integer', 'exists:violations,id'],
                ];

            // The financial report reads a span of the books, so the form says
            // how much of them it is about — a day, the month that day falls
            // in, or its whole year — and names the day to read from. The three
            // spans are written out here rather than kept in a constant: this
            // enum's cases are its own class constants, so an array constant on
            // it would be read as a document.
            case self::FINANCIAL_REPORT:
                return [
                    'periodType' => ['required', 'in:day,month,year'],
                    'periodDate' => ['required', 'date'],
                ];

            // The occupancy list is read by who holds the unit: all of them, or
            // one kind. A unit nobody occupies is not on it, so the filter also
            // says which household types count as an occupant.
            case self::OCCUPANCY_LIST:
                return [
                    'addressId'    => ['nullable', 'integer', 'exists:addresses,id'],
                    'occupantType' => ['nullable', 'in:' . implode(',', HouseholdType::values())],
                ];

            // The summary report is one side of the books for one year: which
            // side its rows are read from, and which year.
            case self::SUMMARY_REPORT:
                return [
                    'accountType' => ['required', 'in:' . implode(',', [AccountType::INCOME, AccountType::EXPENSE])],
                    'year'        => ['required', 'digits:4'],
                ];
        }

        return [];
    }
}
