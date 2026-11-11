<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Violation;
use App\Enums\PrintDocument;
use App\Queries\Settings\PrintSettingsQuery;
use App\Services\PrintDocuments\PrintDocumentService;
use App\Utility\PdfBuilder;

/**
 * The Support pages — the association's requests, violations and complaints —
 * plus the printed notice a violation can be handed to a homeowner as.
 *
 * Every action is a thin shell: authorize, then hand the page to its Livewire
 * component. The one exception is the printable notice, which is a normal route
 * because Livewire cannot stream a download. A request is not printed here:
 * its Print action opens the existing Print Documents page with the request's
 * own details filled in, so the officer reviews the document before printing it.
 */
class SupportController extends Controller
{
    /**
     * Show the Requests page — what homeowners have asked the office for and
     * where each one stands.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function requests()
    {
        $this->authorize('viewAny', \App\Models\Request::class);

        return view('admin.support.requests');
    }

    /**
     * Show the Violations page — the citations the office has issued against
     * homeowners and where each one stands.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function violations()
    {
        $this->authorize('viewAny', Violation::class);

        return view('admin.support.violations');
    }

    /**
     * Show the Complaints page — everything filed with the association, from
     * the portal or taken down by the office.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function complaints()
    {
        $this->authorize('viewAny', Complaint::class);

        return view('admin.support.complaints');
    }

    /**
     * Print one violation as a Notice of Violation.
     *
     * The page's view hands off to this route, which builds the document
     * through PdfBuilder like every other printable record in the app. The
     * letterhead reads the association's own settings — the same
     * GeneralSettingsQuery the footer, the location section and the About
     * page read — so what the notice prints cannot disagree with what the
     * office has on file.
     *
     * Like the journal voucher, the notice is built by the document's own
     * builder, so what the office hands the homeowner is what the Print
     * Documents page draws.
     *
     * @param  int  $id
     * @param  \App\Services\PrintDocuments\PrintDocumentService  $printDocuments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function violationNotice($id, PrintDocumentService $printDocuments)
    {
        $this->authorize('viewAny', Violation::class);

        $document = PrintDocument::VIOLATION_NOTICE();
        $violation = Violation::findOrFail((int) $id);

        $data = $printDocuments->build($document, ['violationId' => $id], app(PrintSettingsQuery::class)->letterhead());

        return PdfBuilder::download(
            $document->view(),
            $data,
            PdfBuilder::filename('Notice-Of-Violation-' . $violation->id . '-' . $violation->type_label),
            $data['letterhead']['paper'] ?? $document->paper(),
            $printDocuments->orientation($document)
        );
    }
}
