<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Queries\Documents\DocumentStaffQuery;
use App\Services\PrintDocuments\AbstractPrintDocumentBuilder;
use App\Services\PrintDocuments\BusinessPermitBuilder;
use App\Services\PrintDocuments\CertificationBuilder;
use App\Services\PrintDocuments\EndorsementLetterBuilder;
use App\Services\PrintDocuments\FinancialReportBuilder;
use App\Services\PrintDocuments\HoaPermitBuilder;
use App\Services\PrintDocuments\InvitationLetterBuilder;
use App\Services\PrintDocuments\JournalVoucherBuilder;
use App\Services\PrintDocuments\MembershipFormBuilder;
use App\Services\PrintDocuments\OccupancyListBuilder;
use App\Services\PrintDocuments\ResidentsListBuilder;
use App\Services\PrintDocuments\SinumpaangSalaysayBuilder;
use App\Services\PrintDocuments\SummaryReportBuilder;
use App\Services\PrintDocuments\ViolationNoticeBuilder;
use App\Utility\PrintDocumentsHelper;
use Illuminate\Contracts\Container\Container;

/**
 * The association's printable documents, and the view data each one draws from.
 *
 * This service does one thing: it turns "print the business permit, from these
 * parameters" into the array the permit's view reads. Each document has a
 * builder of its own — see `App\Services\PrintDocuments` — and this is
 * the registry that points a document at its builder and merges the chrome
 * every document shares (the letterhead, the day it was printed, who printed
 * it, and the fill-in line a blank field prints).
 *
 * Nothing here formats a value and nothing here runs a query: a builder owns
 * its document's parameters, its records and its view-model, and it validates
 * its parameters before it draws anything, so a document that is missing what
 * it needs is an error on the print page rather than a half-empty permit.
 */
class PrintDocumentService
{
    /**
     * The builder that draws each document.
     *
     * One document, one class: adding a document means adding its builder and
     * one line here, not another arm on a switch.
     *
     * @var array<string, class-string<\App\Services\PrintDocuments\PrintDocumentBuilder>>
     */
    const BUILDERS = [
        PrintDocument::MEMBERSHIP_FORM     => MembershipFormBuilder::class,
        PrintDocument::RESIDENTS_LIST      => ResidentsListBuilder::class,
        PrintDocument::BUSINESS_PERMIT     => BusinessPermitBuilder::class,
        PrintDocument::HOA_PERMIT          => HoaPermitBuilder::class,
        PrintDocument::ENDORSEMENT_LETTER  => EndorsementLetterBuilder::class,
        PrintDocument::CERTIFICATION       => CertificationBuilder::class,
        PrintDocument::INVITATION_LETTER   => InvitationLetterBuilder::class,
        PrintDocument::SINUMPAANG_SALAYSAY => SinumpaangSalaysayBuilder::class,
        PrintDocument::JOURNAL_VOUCHER     => JournalVoucherBuilder::class,
        PrintDocument::VIOLATION_NOTICE    => ViolationNoticeBuilder::class,
        PrintDocument::FINANCIAL_REPORT    => FinancialReportBuilder::class,
        PrintDocument::SUMMARY_REPORT      => SummaryReportBuilder::class,
        PrintDocument::OCCUPANCY_LIST      => OccupancyListBuilder::class,
    ];

    /** @var \Illuminate\Contracts\Container\Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    // =====================================================================
    // Build
    // =====================================================================

    /**
     * The view data for one document, from the parameters its form collected.
     *
     * The letterhead is handed in rather than read here: it is the page's data
     * (\App\Queries\Settings\PrintSettingsQuery), and a service that reads it
     * would be reading a view's own source. Every route that prints passes it.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $letterhead
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException when the document is missing a parameter it needs
     */
    public function build(PrintDocument $document, array $params, array $letterhead = []): array
    {
        $builder = $this->builder($document);

        // The document's own view-model first, then the chrome every document
        // shares — so the letterhead and the date printed can never be
        // overwritten by a document's data.
        return array_merge($builder->build($params), $this->common($document, $builder, $params, $letterhead));
    }

    /**
     * The orientation to lay the document out in — the residents list runs
     * across the sheet, every other document down it.
     */
    public function orientation(PrintDocument $document): string
    {
        return $this->builder($document)->orientation();
    }

    /**
     * The document as the paper prints it with nothing filled in.
     *
     * The paper forms the office hands out at the desk: only a document that
     * carries a blank form answers here, and one that does not is not a page.
     *
     * @param  array<string, mixed>  $letterhead
     * @return array<string, mixed>
     */
    public function blank(PrintDocument $document, array $letterhead = []): array
    {
        $builder = $this->builder($document);
        $data = $builder->blank();

        abort_if($data === [], 404);

        return array_merge($data, $this->common($document, $builder, ['blank' => true], $letterhead));
    }

    /**
     * What every document reads, whichever document it is.
     *
     * The letterhead, the blank a document prints where it was given nothing,
     * the community and the office it belongs to, and when and by whom it was
     * printed. A builder never states these: they are the association's, not
     * one document's.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function common(
        PrintDocument $document,
        AbstractPrintDocumentBuilder $builder,
        array $params,
        array $letterhead
    ): array {
        return [
            'document'    => $document,
            'letterhead'  => $letterhead,
            'staff'       => $this->container->make(DocumentStaffQuery::class)->build(),
            'printedAt'   => now(),
            'generatedBy' => $this->printedBy(),
            'isSample'    => (bool) ($params['isSample'] ?? false),
            'blank'       => PrintDocumentsHelper::BLANK,
            'community'   => (string) config('thha.community'),
            'hoaOffice'   => (string) config('thha.address'),
            'orientation' => $builder->orientation(),
        ];
    }

    /**
     * The officer the document was printed by, as its record line reads it.
     */
    private function printedBy(): string
    {
        $admin = auth()->user();

        return $admin ? (string) $admin->name : '';
    }

    /**
     * The builder that draws a document, resolved from the container so a
     * builder may be given the services it needs.
     */
    private function builder(PrintDocument $document): AbstractPrintDocumentBuilder
    {
        $class = self::BUILDERS[$document->value] ?? null;

        abort_if($class === null, 404);

        return $this->container->make($class, ['document' => $document]);
    }
}
