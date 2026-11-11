<?php

namespace Tests\Feature;

use App\Enums\PrintDocument;
use App\Models\Account\Account;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The Print Documents pages and the PDFs they draw.
 *
 * Two halves. The first prints each of the documents from sample data
 * and asserts that the page's parameters reach the document's own builder and
 * come back as a real PDF — including the paper forms that may be printed
 * blank, and the one that prints across the sheet. The second asserts the other
 * side of the same rule: a document that is missing a parameter it requires
 * comes back as an error, never as a half-empty permit.
 */
class PrintDocumentsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Resident */
    private $head;

    /** @var \App\Models\Resident */
    private $spouse;

    /** @var \App\Models\Resident */
    private $respondent;

    /** @var \App\Models\Property */
    private $unit;

    /** @var \App\Models\JournalEntry\JournalEntry */
    private $entry;

    /** @var \App\Models\Violation */
    private $violation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInAdmin();
        $this->sampleData();
    }

    /**
     * The documents whose parameters name a record the office has to have: a
     * resident, a journal entry, a violation.
     *
     * @return array<string, mixed>
     */
    private function sampleParams(string $document): array
    {
        switch ($document) {
            case PrintDocument::RESIDENTS_LIST:
                return ['addressId' => $this->unit->address_id];

            case PrintDocument::JOURNAL_VOUCHER:
                return ['journalEntryId' => $this->entry->id];

            case PrintDocument::VIOLATION_NOTICE:
                return ['violationId' => $this->violation->id];
        }

        return array_merge(['residentId' => $this->head->id], $this->validParams($document));
    }

    // =====================================================================
    // Every document prints
    // =====================================================================

    /**
     * @dataProvider documents
     */
    public function test_every_document_prints_from_its_own_parameters(string $document, array $params): void
    {
        $response = $this->get(route('print.pdf', ['document' => $document]) . '?' . http_build_query(array_merge($this->sampleParams($document), $params)));

        $response->assertOk();
        $this->assertPdf($response->getContent(), $document);
    }

    /**
     * The parameters a form collects for each document, as the contract states
     * them — one entry per document, so a document that stops printing is named.
     *
     * @return array<string, array<int, mixed>>
     */
    public function documents(): array
    {
        return [
            'Membership Form'     => [PrintDocument::MEMBERSHIP_FORM, ['memberType' => 'owner']],
            'Residents List'      => [PrintDocument::RESIDENTS_LIST, ['sortBy' => 'block']],
            'Occupancy List'      => [PrintDocument::OCCUPANCY_LIST, ['occupantType' => 'owner']],
            'Business Permit'     => [PrintDocument::BUSINESS_PERMIT, ['purpose' => 'business_permit']],
            'HOA Permit'          => [PrintDocument::HOA_PERMIT, ['purpose' => 'Roof repair and interior repainting', 'contractor' => 'JM Builders', 'workers' => 4, 'eventFrom' => '2026-10-05', 'eventTo' => '2026-10-19']],
            'Endorsement Letter'  => [PrintDocument::ENDORSEMENT_LETTER, ['addresseeName' => 'Hon. Rosendo Dilidili', 'addresseePosition' => 'Barangay Chairperson', 'addresseeOffice' => 'Brgy. Inocencio', 'problem' => 'excessive noise (videoke) beyond quiet hours']],
            'Certification'       => [PrintDocument::CERTIFICATION, ['purpose' => 'residency']],
            'Invitation Letter'   => [PrintDocument::INVITATION_LETTER, ['concern' => 'a concern that needs to fix', 'meetingDate' => '2026-10-05', 'meetingTime' => '15:00']],
            'Sinumpaang Salaysay' => [PrintDocument::SINUMPAANG_SALAYSAY, ['incidentDate' => '2026-09-25', 'incidentTime' => '21:30', 'narrative' => 'Nakarinig ako ng napakalakas na tugtog.']],
            'Journal Voucher'     => [PrintDocument::JOURNAL_VOUCHER, []],
            'Violation Notice'    => [PrintDocument::VIOLATION_NOTICE, []],
            'Financial Report'    => [PrintDocument::FINANCIAL_REPORT, ['periodType' => 'month', 'periodDate' => '2026-09-30']],
            'Summary Report'      => [PrintDocument::SUMMARY_REPORT, ['accountType' => 'income', 'year' => '2026']],
        ];
    }

    // =====================================================================
    // The blank paper forms
    // =====================================================================

    /**
     * The membership form is paper the office hands out, so it prints with
     * nothing chosen at all.
     */
    public function test_the_membership_form_prints_blank(): void
    {
        $response = $this->get(route('print.pdf', ['document' => PrintDocument::MEMBERSHIP_FORM]));

        $response->assertOk();
        $this->assertPdf($response->getContent(), PrintDocument::MEMBERSHIP_FORM);
    }

    /**
     * The paper forms the office hands out print with nothing chosen at all:
     * the sheet carries whatever the record holds, and every detail the office
     * has not supplied stays a blank line rather than stopping the print.
     */
    public function test_the_paper_forms_print_without_any_details(): void
    {
        foreach ([
            PrintDocument::BUSINESS_PERMIT,
            PrintDocument::HOA_PERMIT,
            PrintDocument::ENDORSEMENT_LETTER,
            PrintDocument::CERTIFICATION,
            PrintDocument::INVITATION_LETTER,
            PrintDocument::SINUMPAANG_SALAYSAY,
        ] as $document) {
            $response = $this->get(route('print.pdf', ['document' => $document]));

            $response->assertOk();
            $this->assertPdf($response->getContent(), $document);
        }
    }

    // =====================================================================
    // The residents list
    // =====================================================================

    /**
     * The residents list runs across the sheet: an A4 sheet on its side is
     * 297mm wide by 210mm tall, which is what the PDF's own media box says.
     */
    public function test_the_residents_list_prints_landscape(): void
    {
        $response = $this->get(route('print.pdf', ['document' => PrintDocument::RESIDENTS_LIST]) . '?' . http_build_query(['addressId' => $this->unit->address_id]));

        $response->assertOk();
        $this->assertPrintsAcrossTheSheet($response->getContent(), 'Residents List');
    }

    /**
     * The list is the association's households, each in its own unit.
     */
    public function test_the_residents_list_prints_the_households_on_file(): void
    {
        $data = $this->service()->build(PrintDocument::RESIDENTS_LIST(), ['addressId' => $this->unit->address_id, 'sortBy' => 'block']);

        $this->assertSame(2, $data['totals']['properties']);
        $this->assertSame(2, $data['totals']['households']);
        $this->assertSame(['Blk 4 · Lot 12', 'Blk 6 · Lot 3'], $data['rows']->pluck('blockLot')->all());
        $this->assertNotSame('Head', $data['rows'][0]['members'][0]['relationship']);
    }

    /**
     * A row is a household list, so a unit nobody lives in is not one of them:
     * the empty units of an address are never read, which is also what keeps a
     * whole address quick to draw.
     */
    public function test_the_residents_list_leaves_out_the_units_nobody_lives_in(): void
    {
        Property::create([
            'block'      => 8,
            'lot'        => 1,
            'address_id' => $this->unit->address_id,
        ]);

        $data = $this->service()->build(PrintDocument::RESIDENTS_LIST(), ['addressId' => $this->unit->address_id]);

        $this->assertSame(2, $data['totals']['properties']);
        $this->assertNotContains('Blk 8 · Lot 1', $data['rows']->pluck('blockLot')->all());
    }

    /**
     * The two numbers are the "No." column's own span, so one address can be
     * printed in batches. The numbers stay the row's place in the whole list
     * rather than in the batch, so the second batch carries on where the first
     * one stopped.
     */
    public function test_the_residents_list_prints_the_numbers_the_form_asked_for(): void
    {
        $service = $this->service();

        $first = $service->build(PrintDocument::RESIDENTS_LIST(), [
            'addressId' => $this->unit->address_id,
            'startNo'   => 1,
            'endNo'     => 1,
        ]);

        $this->assertSame(['Blk 4 · Lot 12'], $first['rows']->pluck('blockLot')->all());
        $this->assertSame('1–1 of 2', $first['filters']['numbers']);
        $this->assertSame(1, $first['totals']['properties']);
        $this->assertSame(5, $first['totals']['residents']);
        $this->assertSame(1, $first['totals']['households']);

        // An end number on its own is a span from the first unit; a start number
        // on its own runs to the last one.
        $second = $service->build(PrintDocument::RESIDENTS_LIST(), [
            'addressId' => $this->unit->address_id,
            'startNo'   => 2,
        ]);

        $this->assertSame(['Blk 6 · Lot 3'], $second['rows']->pluck('blockLot')->all());
        $this->assertSame('2–2 of 2', $second['filters']['numbers']);

        $untilOne = $service->build(PrintDocument::RESIDENTS_LIST(), [
            'addressId' => $this->unit->address_id,
            'endNo'     => 1,
        ]);

        $this->assertSame(['Blk 4 · Lot 12'], $untilOne['rows']->pluck('blockLot')->all());
    }

    /**
     * The page asks for the numbers and the builder carries them: a range the
     * form offers is a range the PDF draws.
     */
    public function test_the_residents_list_page_asks_for_the_numbers(): void
    {
        $response = $this->get(route('print.show', ['document' => PrintDocument::RESIDENTS_LIST]));

        $response->assertOk();
        $response->assertSee('name="startNo"', false);
        $response->assertSee('name="endNo"', false);
    }

    // =====================================================================
    // The financial report
    // =====================================================================

    /**
     * The span the officer asked for is the span the books are read over, and
     * the sheet says which span it printed.
     */
    public function test_the_financial_report_reads_the_span_the_form_asked_for(): void
    {
        $service = $this->service();

        $day = $service->build(PrintDocument::FINANCIAL_REPORT(), ['periodType' => 'day', 'periodDate' => '2026-09-15']);
        $this->assertSame(['2026-09-15', '2026-09-15'], [$day['period']['from'], $day['period']['to']]);
        $this->assertSame('For September 15, 2026', $day['period']['heading']);

        $month = $service->build(PrintDocument::FINANCIAL_REPORT(), ['periodType' => 'month', 'periodDate' => '2026-09-15']);
        $this->assertSame(['2026-09-01', '2026-09-30'], [$month['period']['from'], $month['period']['to']]);
        $this->assertSame('For the month of September 2026', $month['period']['heading']);

        $year = $service->build(PrintDocument::FINANCIAL_REPORT(), ['periodType' => 'year', 'periodDate' => '2026-09-15']);
        $this->assertSame(['2026-01-01', '2026-12-31'], [$year['period']['from'], $year['period']['to']]);
        $this->assertSame('For the year 2026', $year['period']['heading']);
    }

    /**
     * The report closes with the cash accounts' own balances at the end of the
     * span, split the way it reads them: the account kept for money in hand is
     * the cash on hand, and every other cash account is money in the bank.
     */
    public function test_the_financial_report_splits_the_cash_in_the_bank_from_the_cash_on_hand(): void
    {
        // 500 of dues collected into the cash box, on top of the 1,500 the
        // sample posting put in the bank account.
        $this->collectDues(500, '2026-09-28', $this->incomeAccount('4000', 'Monthly Dues'));

        $data = $this->service()->build(PrintDocument::FINANCIAL_REPORT(), [
            'periodType' => 'month',
            'periodDate' => '2026-09-30',
        ]);

        $this->assertSame(['On hand', 'In bank'], array_column($data['cash']['rows'], 'where'));
        $this->assertSame('PHP 2,000.00', $data['cash']['total']);
        $this->assertSame('PHP 1,500.00', $data['cash']['in_bank']);
        $this->assertSame('PHP 500.00', $data['cash']['on_hand']);

        // What the same span earned, and what it came to.
        $this->assertSame('PHP 500.00', $data['income']['total']);
        $this->assertSame('PHP 0.00', $data['expenses']['total']);
        $this->assertSame('PHP 500.00', $data['net']['amount']);
    }

    // =====================================================================
    // The summary report
    // =====================================================================

    /**
     * The summary report is the year as a matrix: a row per account with a
     * column per month, a total at the end of every row and under every month.
     *
     * Each account carries its own months and nothing of its neighbours' — two
     * accounts earning in different months is what a row total and a month total
     * are read against.
     */
    public function test_the_summary_report_lays_the_year_out_by_account_and_month(): void
    {
        $this->collectDues(1000, '2026-01-15', $this->incomeAccount('4000', 'Monthly Dues'));
        $this->collectDues(250, '2026-03-10', $this->incomeAccount('4100', 'Fees and Charges'));

        // A third income account with nothing posted to it all year — the sheet
        // is read for what moved, so it is not a line of it.
        $this->incomeAccount('4200', 'Penalties');

        $data = $this->service()->build(PrintDocument::SUMMARY_REPORT(), [
            'accountType' => 'income',
            'year'        => '2026',
        ]);

        $this->assertSame('Income', $data['type']);
        $this->assertSame('INCOME BY MONTH', $data['heading']);
        $this->assertSame('For the year 2026', $data['span']);
        $this->assertCount(2, $data['rows']);

        $dues = $data['rows'][0];

        $this->assertSame('4000', $dues['code']);
        $this->assertSame('1,000.00', $dues['months'][0]);
        $this->assertSame('—', $dues['months'][1]);
        $this->assertSame('—', $dues['months'][2]);
        $this->assertSame('1,000.00', $dues['total']);

        $fees = $data['rows'][1];

        $this->assertSame('4100', $fees['code']);
        $this->assertSame('—', $fees['months'][0]);
        $this->assertSame('250.00', $fees['months'][2]);
        $this->assertSame('250.00', $fees['total']);

        // The closing row: what each month came to, and what the year did.
        $this->assertSame('1,000.00', $data['months'][0]);
        $this->assertSame('—', $data['months'][1]);
        $this->assertSame('250.00', $data['months'][2]);
        $this->assertSame('1,250.00', $data['year_total']);

        // The other side of the books reads the same way, and none is on file.
        $expenses = $this->service()->build(PrintDocument::SUMMARY_REPORT(), [
            'accountType' => 'expense',
            'year'        => '2026',
        ]);

        $this->assertSame('Expense', $expenses['type']);
        $this->assertSame([], $expenses['rows']);
        $this->assertSame('—', $expenses['year_total']);
    }

    /**
     * Thirteen money columns and an account do not fit an A4 sheet held the tall
     * way, so the summary report runs across it.
     */
    public function test_the_summary_report_prints_landscape(): void
    {
        $response = $this->get(route('print.pdf', ['document' => PrintDocument::SUMMARY_REPORT]) . '?' . http_build_query([
            'accountType' => 'income',
            'year'        => '2026',
        ]));

        $response->assertOk();
        $this->assertPrintsAcrossTheSheet($response->getContent(), 'Summary Report');
    }

    // =====================================================================
    // The invitation to everyone
    // =====================================================================

    /**
     * An invitation to every homeowner is one sheet per household head with an
     * account — the two households here, and not the residents who have none.
     */
    public function test_the_invitation_prints_one_sheet_per_homeowner(): void
    {
        $data = $this->service()->build(PrintDocument::INVITATION_LETTER(), [
            'allHomeowners' => true,
            'concern'       => 'a concern that needs to fix',
            'meetingDate'   => '2026-10-05',
            'meetingTime'   => '15:00',
        ]);

        $this->assertCount(2, $data['recipients']);
        $this->assertSame('Juan S. Dela Cruz', $data['recipients'][0]['name']);
        $this->assertSame('Block 4 Lot 12, Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite', $data['recipients'][0]['address']);

        $response = $this->get(route('print.pdf', ['document' => PrintDocument::INVITATION_LETTER]) . '?' . http_build_query([
            'allHomeowners' => 1,
            'concern'       => 'a concern that needs to fix',
            'meetingDate'   => '2026-10-05',
            'meetingTime'   => '15:00',
        ]));

        $response->assertOk();
        $this->assertPdf($response->getContent(), PrintDocument::INVITATION_LETTER);
    }

    // =====================================================================
    // What a document requires
    // =====================================================================

    /**
     * Every document that may not print blank answers an error when the
     * parameter it is missing is missing — the print page shows the fault
     * instead of a half-empty document.
     *
     * @dataProvider requiredParameters
     */
    public function test_a_missing_required_parameter_is_a_validation_error(string $document): void
    {
        $response = $this->get(route('print.pdf', ['document' => $document]));

        $response->assertStatus(422);
        $response->assertSee('needs a few details first');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function requiredParameters(): array
    {
        return [
            'Journal Voucher'     => [PrintDocument::JOURNAL_VOUCHER],
            'Violation Notice'    => [PrintDocument::VIOLATION_NOTICE],
            'Financial Report'    => [PrintDocument::FINANCIAL_REPORT],
            'Summary Report'      => [PrintDocument::SUMMARY_REPORT],
        ];
    }

    /**
     * The parameters a document carries are validated as a whole: a purpose the
     * permit's own list does not carry is refused, and "Other" needs the text
     * it stands for.
     */
    public function test_the_business_permit_refuses_a_purpose_outside_its_own_list(): void
    {
        $this->assertValidationFails(PrintDocument::BUSINESS_PERMIT, [
            'residentId' => $this->head->id,
            'purpose'    => 'roof repair',
        ]);
    }

    public function test_other_needs_the_purpose_it_stands_for(): void
    {
        $this->assertValidationFails(PrintDocument::CERTIFICATION, [
            'residentId' => $this->head->id,
            'purpose'    => 'others',
        ]);

        $response = $this->get(route('print.pdf', ['document' => PrintDocument::CERTIFICATION]) . '?' . http_build_query([
            'residentId'   => $this->head->id,
            'purpose'      => 'others',
            'purposeOther' => 'School enrolment',
        ]));

        $response->assertOk();
    }

    /**
     * A letter the association writes on its own behalf names two different
     * people: an endorsement against the complainant themselves is refused.
     */
    public function test_a_statement_cannot_be_made_against_the_affiant(): void
    {
        $this->assertValidationFails(PrintDocument::SINUMPAANG_SALAYSAY, [
            'residentId'    => $this->head->id,
            'respondentId'  => $this->head->id,
            'incidentDate'  => '2026-09-25',
            'incidentTime'  => '21:30',
            'narrative'     => 'Isang salaysay.',
        ]);
    }

    /**
     * A resident the office does not have is refused, so a document is never
     * drawn against a record that is not there.
     */
    public function test_a_document_refuses_a_resident_the_office_does_not_have(): void
    {
        $this->assertValidationFails(PrintDocument::CERTIFICATION, [
            'residentId' => $this->head->id + 100000,
            'purpose'    => 'residency',
        ]);
    }

    // =====================================================================
    // The pages
    // =====================================================================

    /**
     * Each document has a page, and the page offers its own parameters.
     */
    public function test_every_document_has_a_page(): void
    {
        foreach (PrintDocument::cases() as $document) {
            $response = $this->get(route('print.show', ['document' => $document->slug()]));

            $response->assertOk();
            $response->assertSee($document->label());
        }
    }

    public function test_the_business_permit_page_offers_its_purposes_and_no_removed_field(): void
    {
        $response = $this->get(route('print.show', ['document' => PrintDocument::BUSINESS_PERMIT]));

        $response->assertOk();
        $response->assertSee('Electrical Connection');
        $response->assertSee('name="controlNumber"', false);

        // The paper permit has no room for these, so the page does not ask.
        $response->assertDontSee('name="businessName"', false);
        $response->assertDontSee('name="nature"', false);
        $response->assertDontSee('name="validUntil"', false);
        $response->assertDontSee('name="fee"', false);
    }

    /**
     * The two report pages ask for what their papers are read over: the span of
     * the books for the financial report, and the side of them plus the year for
     * the summary report — the years on offer being the ones the journal covers.
     */
    public function test_the_report_pages_ask_for_the_period_and_the_year(): void
    {
        $financial = $this->get(route('print.show', ['document' => PrintDocument::FINANCIAL_REPORT]));

        $financial->assertOk();
        $financial->assertSee('name="periodType"', false);
        $financial->assertSee('name="periodDate"', false);

        $summary = $this->get(route('print.show', ['document' => PrintDocument::SUMMARY_REPORT]));

        $summary->assertOk();
        $summary->assertSee('name="accountType"', false);
        $summary->assertSee('name="year"', false);
        $summary->assertSee('value="2026"', false);
    }

    public function test_the_invitation_page_collects_what_the_letter_prints(): void
    {
        $response = $this->get(route('print.show', ['document' => PrintDocument::INVITATION_LETTER]));

        $response->assertOk();
        $response->assertSee('name="concern"', false);
        $response->assertSee('name="meetingDate"', false);
        $response->assertSee('name="meetingTime"', false);

        $response->assertDontSee('name="agendaLines"', false);
        $response->assertDontSee('name="rsvpDate"', false);
    }

    /**
     * The voucher and the notice are printed from their own modules' pages too,
     * through the same builders — so the paper is identical whichever door it
     * was asked for from (and neither module draws its own view-model).
     */
    public function test_a_record_page_prints_its_document_through_the_same_builder(): void
    {
        $voucher = $this->get(route('accounting.journal-entries.voucher', ['id' => $this->entry->id]));
        $voucher->assertOk();
        $this->assertPdf($voucher->getContent(), 'Journal Voucher');

        $notice = $this->get(route('support.violations.notice', ['id' => $this->violation->id]));
        $notice->assertOk();
        $this->assertPdf($notice->getContent(), 'Violation Notice');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The service the route builds documents with.
     */
    private function service(): \App\Services\PrintDocuments\PrintDocumentService
    {
        return $this->app->make(\App\Services\PrintDocuments\PrintDocumentService::class);
    }

    /**
     * What each document's form collects, for the documents that need a
     * resident named on them.
     *
     * @return array<string, mixed>
     */
    private function validParams(string $document): array
    {
        switch ($document) {
            case PrintDocument::ENDORSEMENT_LETTER:
                return [
                    'respondentId'       => $this->respondent->id,
                    'addresseeName'      => 'Hon. Rosendo Dilidili',
                    'addresseePosition'  => 'Barangay Chairperson',
                    'addresseeOffice'    => 'Brgy. Inocencio',
                    'problem'            => 'excessive noise (videoke) beyond quiet hours',
                    'mediationAttempted' => 1,
                ];

            case PrintDocument::INVITATION_LETTER:
                return [
                    'concern'     => 'a concern that needs to fix',
                    'meetingDate' => '2026-10-05',
                    'meetingTime' => '15:00',
                ];

            case PrintDocument::HOA_PERMIT:
                return [
                    'purpose'   => 'Roof repair and interior repainting',
                    'eventFrom' => '2026-10-05',
                ];

            case PrintDocument::BUSINESS_PERMIT:
                return ['purpose' => 'business_permit'];

            case PrintDocument::CERTIFICATION:
                return ['purpose' => 'residency'];

            case PrintDocument::SINUMPAANG_SALAYSAY:
                return ['respondentId' => $this->respondent->id];

            case PrintDocument::MEMBERSHIP_FORM:
                return ['memberType' => 'owner'];
        }

        return [];
    }

    /**
     * A document that has to fit a table across the sheet prints across it: an
     * A4 sheet on its side is 297mm wide by 210mm tall, which is what the PDF's
     * own media box says.
     */
    private function assertPrintsAcrossTheSheet(string $content, string $document): void
    {
        $this->assertSame(1, preg_match('/MediaBox\s*\[\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\]/', $content, $matches),
            $document . ' did not state a media box.');

        $width = (float) $matches[3] - (float) $matches[1];
        $height = (float) $matches[4] - (float) $matches[2];

        $this->assertGreaterThan($height, $width, $document . ' did not print across an A4 sheet.');
    }

    /**
     * A document's own build, asserting the response is a document with a page
     * in it rather than the word "PDF" written over an error.
     */
    private function assertPdf(string $content, string $document): void
    {
        $this->assertStringStartsWith('%PDF-', $content, $document . ' did not produce a PDF');

        // A PDF that failed to lay out is a few hundred bytes of header.
        $this->assertGreaterThan(20000, strlen($content), $document . ' produced an empty PDF');
    }

    /**
     * A document naming a resident the request left out answers a fault.
     *
     * @param  array<string, mixed>  $params
     */
    private function assertValidationFails(string $document, array $params): void
    {
        $response = $this->get(route('print.pdf', ['document' => $document]) . '?' . http_build_query($params));

        $response->assertStatus(422);
    }

    // =====================================================================
    // The office's own records
    // =====================================================================

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Maria Clara Villanueva',
            'email'               => 'print-documents-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * The two households the references were drawn from: the Dela Cruz family
     * of Blk 4 Lot 12, and Anna Marie Lim of Blk 6 Lot 3.
     */
    private function sampleData(): void
    {
        $address = Address::create([
            'label'       => 'TH 2',
            'subdivision' => 'Tradition Homes Ph-2',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'zip_code'    => '4107',
        ]);

        $this->unit = Property::create([
            'block'      => 4,
            'lot'        => 12,
            'address_id' => $address->id,
            'sqm'        => 120,
            'type'       => 'house',
        ]);

        $this->head = $this->resident([
            'surname'      => 'Dela Cruz',
            'first_name'   => 'Juan',
            'middle_name'  => 'Santos',
            'birth_date'   => '1985-06-14',
            'birth_place'  => 'Imus, Cavite',
            'sex'          => 'male',
            'civil_status' => 'married',
            'occupation'   => 'Engineer',
            'mobile_no'    => '09181234567',
        ]);

        $this->spouse = $this->resident([
            'surname'      => 'Dela Cruz',
            'first_name'   => 'Rosario',
            'middle_name'  => 'Bautista',
            'birth_date'   => '1988-02-09',
            'sex'          => 'female',
            'civil_status' => 'married',
            'occupation'   => 'Business Owner',
        ]);

        $son = $this->resident(['surname' => 'Dela Cruz', 'first_name' => 'Mark Anthony', 'middle_name' => 'Santos', 'birth_date' => '2010-11-23', 'sex' => 'male', 'civil_status' => 'single']);
        $daughter = $this->resident(['surname' => 'Dela Cruz', 'first_name' => 'Angeline', 'middle_name' => 'Santos', 'birth_date' => '2013-05-02', 'sex' => 'female', 'civil_status' => 'single']);
        $mother = $this->resident(['surname' => 'Dela Cruz', 'first_name' => 'Lucia', 'middle_name' => 'Reyes', 'birth_date' => '1954-09-30', 'sex' => 'female', 'civil_status' => 'widowed', 'occupation' => 'Retired']);

        factory(User::class)->create([
            'resident_id' => $this->head->id,
            'email'       => 'juan.delacruz@example.test',
        ]);

        $household = Household::create([
            'property_id'      => $this->unit->id,
            'type'             => 'owner',
            'prev_address'     => 'Blk 2 Lot 8, Sample Village, Imus, Cavite',
        ]);

        $household->placeHead($this->head->id, 'father');

        foreach ([[$this->spouse, 'mother'], [$son, 'son'], [$daughter, 'daughter'], [$mother, 'grandmother']] as [$resident, $relationship]) {
            HouseholdMember::create([
                'household_id' => $household->id,
                'resident_id'  => $resident->id,
                'relationship' => $relationship,
            ]);
        }

        // The other household — the one an endorsement is written against.
        $limUnit = Property::create([
            'block'      => 6,
            'lot'        => 3,
            'address_id' => $address->id,
            'sqm'        => 200,
            'type'       => 'house',
        ]);

        $this->respondent = $this->resident([
            'surname'      => 'Lim',
            'first_name'   => 'Anna Marie',
            'middle_name'  => 'Tan',
            'birth_date'   => '1991-01-05',
            'sex'          => 'female',
            'civil_status' => 'married',
        ]);

        factory(User::class)->create([
            'resident_id' => $this->respondent->id,
            'email'       => 'anna.lim@example.test',
        ]);

        $limHousehold = Household::create([
            'property_id'      => $limUnit->id,
            'type'             => 'owner',
        ]);

        $limHousehold->placeHead($this->respondent->id, 'mother');

        $this->sampleBookEntry();
        $this->sampleViolation();
    }

    /**
     * One balanced posting, as the voucher prints it.
     */
    private function sampleBookEntry(): void
    {
        $cash = Account::create(['code' => '1010', 'name' => 'Cash in Bank — BPI', 'type' => 'asset', 'is_cash' => true]);
        $receivable = Account::create(['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset']);

        $this->entry = JournalEntry::create([
            'entry_no'       => 'JE-2026-000123',
            'entry_date'     => '2026-09-30',
            'classification' => 'standard',
            'status'         => 'posted',
            'reference_no'   => 'OR-004533',
            'memo'           => 'Payment of monthly dues for September 2026 — Blk 4 Lot 12',
            'source_type'    => 'payment',
        ]);

        JournalLine::create([
            'journal_entry_id' => $this->entry->id,
            'account_id'       => $cash->id,
            'account_name'     => $cash->name,
            'side'             => JournalLine::SIDE_DEBIT,
            'amount'           => 1500,
            'memo'             => 'Payment received',
        ]);

        JournalLine::create([
            'journal_entry_id' => $this->entry->id,
            'account_id'       => $receivable->id,
            'account_name'     => $receivable->name,
            'side'             => JournalLine::SIDE_CREDIT,
            'amount'           => 1500,
            'memo'             => 'Monthly due Sep 2026',
        ]);
    }

    /**
     * An income account of the chart, as the office keeps them.
     */
    private function incomeAccount(string $code, string $name): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => 'income']
        );
    }

    /**
     * A homeowner paying over the counter: the money lands in the account kept
     * for cash in hand and the income is earned on the other side of the same
     * posting, so the reports have a balanced entry to read.
     */
    private function collectDues(float $amount, string $date, Account $income): void
    {
        $cash = Account::cashOnHand();

        $entry = JournalEntry::create([
            'entry_no'       => 'JE-2026-' . str_pad((string) (JournalEntry::count() + 1), 6, '0', STR_PAD_LEFT),
            'entry_date'     => $date,
            'classification' => 'standard',
            'status'         => 'posted',
            'memo'           => 'Collected over the counter',
            'source_type'    => 'payment',
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $cash->id,
            'account_name'     => $cash->name,
            'side'             => JournalLine::SIDE_DEBIT,
            'amount'           => $amount,
            'memo'             => 'Dues received',
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $income->id,
            'account_name'     => $income->name,
            'side'             => JournalLine::SIDE_CREDIT,
            'amount'           => $amount,
            'memo'             => 'Monthly due',
        ]);
    }

    /**
     * The violation the notice prints, against the second household.
     */
    private function sampleViolation(): void
    {
        $this->violation = Violation::create([
            'resident_id'    => $this->respondent->id,
            'resident_name'  => 'Anna Marie Lim',
            'violation_type' => 'excessive_noise',
            'occurred_on'    => '2026-09-25',
            'occurred_time'  => '21:30',
            'location'       => 'Blk 6 · Lot 3',
            'description'    => 'Loud videoke and music were heard from the property from approximately 9:30 PM until midnight, beyond the quiet hours set by the Association.',
            'status'         => 'active',
            'office_report'  => 'Complaint received from Blk 4 Lot 12 (sworn statement attached).',
            'issued_at'      => '2026-09-26 09:00:00',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resident(array $attributes): Resident
    {
        return factory(Resident::class)->create(array_merge([
            // A suffix is part of a name, and the factory sprinkles them in at
            // random: the sample households state their own names in full.
            'suffix'                 => null,
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ], $attributes));
    }
}
