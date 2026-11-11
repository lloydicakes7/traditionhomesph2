<?php

namespace Tests\Feature\Documents;

use App\Enums\PrintDocument;
use App\Queries\Documents\BusinessPermitDocumentQuery;
use App\Queries\Documents\CertificationDocumentQuery;
use App\Queries\Documents\EndorsementLetterDocumentQuery;
use App\Queries\Documents\HoaPermitDocumentQuery;
use App\Queries\Documents\InvitationLetterDocumentQuery;
use App\Queries\Documents\MembershipFormDocumentQuery;
use App\Queries\Documents\SinumpaangSalaysayDocumentQuery;
use App\Services\PrintDocuments\PrintDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The data contract every printed document states.
 *
 * A document query always answers the same shape: every key exists, strings are
 * empty rather than null, lists are as long as the paper's rows, and a date is
 * its parts. A paper form without its details still prints: every blank stays a
 * blank line on the sheet rather than stopping the print.
 */
class DocumentDataContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string, 1: array<int, string>}>
     */
    public function documents(): array
    {
        return [
            'hoa permit' => [HoaPermitDocumentQuery::class, HoaPermitDocumentQuery::PAPER_FIELDS],
            'endorsement letter' => [EndorsementLetterDocumentQuery::class, EndorsementLetterDocumentQuery::PAPER_FIELDS],
            'invitation letter' => [InvitationLetterDocumentQuery::class, InvitationLetterDocumentQuery::PAPER_FIELDS],
            'certification' => [CertificationDocumentQuery::class, CertificationDocumentQuery::PAPER_FIELDS],
            'sinumpaang salaysay' => [SinumpaangSalaysayDocumentQuery::class, SinumpaangSalaysayDocumentQuery::PAPER_FIELDS],
            'business permit' => [BusinessPermitDocumentQuery::class, BusinessPermitDocumentQuery::PAPER_FIELDS],
        ];
    }

    /**
     * @dataProvider documents
     */
    public function test_the_blank_form_answers_every_paper_field(string $query, array $fields): void
    {
        $blank = app($query)->blank();

        foreach ($fields as $key) {
            $this->assertArrayHasKey($key, $blank, "blank() is missing {$key}");
        }

        foreach ($blank as $key => $value) {
            $this->assertFalse(is_null($value), "blank() returns null for {$key}");
            $this->assertFalse(is_object($value), "blank() returns an object for {$key}");
        }
    }

    /**
     * @dataProvider documents
     */
    public function test_a_document_without_any_data_prints_its_blank_sheet(string $query, array $fields): void
    {
        $blank = app($query)->blank();
        $data = app($query)->build([]);

        $this->assertSame(array_keys($blank), array_keys($data), 'build([]) does not answer the blank sheet\'s shape.');

        foreach ($data as $key => $value) {
            $this->assertFalse(is_null($value), "build([]) returns null for {$key}");
            $this->assertFalse(is_object($value), "build([]) returns an object for {$key}");
        }
    }

    /**
     * The membership form is paper the office fills in at the desk, so it never
     * refuses its own blank sheet.
     */
    public function test_the_membership_form_prints_without_any_record(): void
    {
        $data = app(MembershipFormDocumentQuery::class)->build([]);

        $this->assertSame('', $data['surname']);
        $this->assertSame(MembershipFormDocumentQuery::MEMBER_ROWS, count($data['members']));
    }

    public function test_the_print_service_hands_back_a_blank_form(): void
    {
        $service = app(PrintDocumentService::class);

        foreach ([
            PrintDocument::MEMBERSHIP_FORM(),
            PrintDocument::HOA_PERMIT(),
            PrintDocument::ENDORSEMENT_LETTER(),
            PrintDocument::INVITATION_LETTER(),
            PrintDocument::CERTIFICATION(),
            PrintDocument::SINUMPAANG_SALAYSAY(),
            PrintDocument::BUSINESS_PERMIT(),
        ] as $document) {
            $this->assertTrue($document->hasBlankForm());

            $data = $service->blank($document);

            $this->assertArrayHasKey('letterhead', $data);
            $this->assertArrayHasKey('staff', $data);
            $this->assertGreaterThan(2, count($data), $document->value . ' has no blank paper');
        }
    }

    /**
     * @dataProvider documents
     */
    public function test_a_blank_form_keeps_the_date_shape(string $query, array $fields): void
    {
        $blank = app($query)->blank();

        foreach ($blank as $key => $value) {
            if (strpos($key, 'date') === false) {
                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $this->assertSame(
                ['day', 'dayOrdinal', 'month', 'year', 'yearShort', 'long'],
                array_keys($value),
                "{$key} is not a dateParts array"
            );
        }
    }
}
