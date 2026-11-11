<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\PrintDocuments;

use App\Enums\BusinessPermitPurpose;
use App\Enums\CertificationPurpose;
use App\Enums\HouseholdType;
use App\Enums\PrintDocument;
use App\Http\Livewire\AdminComponent;
use App\Models\Request as RequestModel;
use App\Queries\Documents\MembershipFormDocumentQuery;
use App\Services\PrintDocuments\PrintDocumentOptions;

class DocumentPreview extends AdminComponent
{
    /** The document's slug — the enum value this preview is showing. */
    public $documentSlug;

    /**
     * Values the document's form opens with — the details of the homeowner's
     * request, when the page was opened from the Requests page.
     *
     * @var array<string, mixed>
     */
    public $prefill = [];

    /** The member the Membership Form is about, or blank for empty paper. */
    public $memberId = '';

    /**
     * The Membership Form details shown in the form: the member's own record,
     * as the sheet prints it. A detail the record leaves blank stays blank.
     *
     * @var array<string, string>
     */
    public $details = [];

    /** The homeowner dialog tells this form when the resident list changed. */
    protected $listeners = [
        'homeowner-saved' => '$refresh',
    ];

    /** @var \App\Services\PrintDocuments\PrintDocumentOptions */
    private $options;

    /** @var \App\Queries\Documents\MembershipFormDocumentQuery */
    private $membership;

    public function boot(
        PrintDocumentOptions $options,
        MembershipFormDocumentQuery $membership
    ) {
        $this->options = $options;
        $this->membership = $membership;
    }

    public function mount(string $document)
    {
        $this->authorize('print.viewAny');

        $resolved = PrintDocument::tryFrom($document);

        abort_if($resolved === null, 404);

        $this->documentSlug = $resolved->value;
        $this->prefill = $this->prefillFromRequest($resolved);

        if ($resolved->value === PrintDocument::MEMBERSHIP_FORM) {
            $this->openMembershipForm();
        }
    }

    /**
     * The document this component is showing.
     */
    public function printDocument(): PrintDocument
    {
        return PrintDocument::from($this->documentSlug);
    }

    /**
     * A different member was chosen: the form shows that member's own record.
     */
    public function updatedMemberId($value): void
    {
        $this->authorize('print.viewAny');

        $this->fillDetails((string) $value);
    }

    /**
     * The details a request fills this document with, when the page was opened
     * from the Requests page (`?request={id}`).
     *
     * The request is read only if it exists and its own type maps to the
     * document being shown, so a document can never be seeded with another
     * document's fields. The values are the request's own — the officer reviews
     * them in the form and prints from there, rather than re-entering them.
     *
     * @param  \App\Enums\PrintDocument  $document
     * @return array<string, mixed>
     */
    private function prefillFromRequest(PrintDocument $document): array
    {
        $id = request()->query('request');

        if (! $id) {
            return [];
        }

        $request = RequestModel::find((int) $id);

        if (! $request) {
            return [];
        }

        $this->authorize('view', RequestModel::class);

        $mapped = $request->printDocument();

        if (! $mapped || $mapped->value !== $document->value) {
            return [];
        }

        return array_filter($request->printParams(), function ($value) {
            return $value !== null && $value !== '';
        });
    }

    // =====================================================================
    // Membership Form
    // =====================================================================

    /**
     * Open the sheet with the member the page was handed, or blank.
     */
    private function openMembershipForm(): void
    {
        $this->fillDetails((string) ($this->prefill['residentId'] ?? ''));

        // A detail supplied for the request fills only what the record leaves
        // blank — the printed sheet always prefers the record.
        foreach (array_keys($this->blankDetails()) as $key) {
            if ($this->details[$key] === '' && ! empty($this->prefill[$key])) {
                $this->details[$key] = (string) $this->prefill[$key];
            }
        }
    }

    /**
     * Read the form's details from the member's own record.
     *
     * The values are the printed sheet's own (`MembershipFormDocumentQuery`),
     * so the form and the paper cannot disagree about what the record says.
     */
    private function fillDetails(string $residentId): void
    {
        $this->details = $this->blankDetails();

        if (! is_numeric($residentId) || (int) $residentId < 1) {
            return;
        }

        $profile = $this->membership->preview(['residentId' => (int) $residentId]);

        foreach (array_keys($this->details) as $key) {
            $this->details[$key] = (string) ($profile[$key] ?? '');
        }
    }

    /**
     * The one detail the print page still asks for; the rest of the sheet is
     * read from the member's own record.
     *
     * @return array<string, string>
     */
    private function blankDetails(): array
    {
        return [
            'previous_address' => '',
        ];
    }

    public function render()
    {
        $document = $this->printDocument();

        // Only the option lists this document actually offers are read — the
        // resident picker for the documents that name a person, the address and
        // block filters for the residents list, the years for the summary
        // report, and so on. Every other list is left an empty array, so opening
        // one document never runs the queries a different document would need.
        $usesResidents = ! in_array($document->value, [
            PrintDocument::RESIDENTS_LIST,
            PrintDocument::JOURNAL_VOUCHER,
            PrintDocument::VIOLATION_NOTICE,
            PrintDocument::FINANCIAL_REPORT,
            PrintDocument::SUMMARY_REPORT,
            PrintDocument::OCCUPANCY_LIST,
        ], true);

        $isResidentsList = $document->value === PrintDocument::RESIDENTS_LIST;
        $isSummaryReport = $document->value === PrintDocument::SUMMARY_REPORT;
        $isOccupancyList = $document->value === PrintDocument::OCCUPANCY_LIST;

        return view('livewire.admin.print.document-preview', [
            'document'              => $document,
            'pdfUrl'                => route('print.pdf', ['document' => $document->slug()]),
            'residents'             => $usesResidents ? $this->options->residentOptions() : [],
            'addresses'             => $isResidentsList ? $this->options->addressOptions() : [],
            'blocks'                => $isResidentsList ? $this->options->blockOptions() : [],
            'years'                 => $isSummaryReport ? $this->options->yearOptions() : [],
            'householdTypes'        => $isOccupancyList ? HouseholdType::labels() : [],
            'journalEntries'        => $document->value === PrintDocument::JOURNAL_VOUCHER ? $this->options->journalEntryOptions() : [],
            'violations'            => $document->value === PrintDocument::VIOLATION_NOTICE ? $this->options->violationOptions() : [],
            'permitPurposes'        => BusinessPermitPurpose::labels(),
            'certificationPurposes' => CertificationPurpose::labels(),
            'today'                 => now()->format('Y-m-d'),
        ]);
    }
}
