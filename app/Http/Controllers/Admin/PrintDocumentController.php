<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PrintDocument;
use App\Exceptions\MissingDocumentDataException;
use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Services\PrintDocuments\PrintDocumentService;
use App\Queries\Settings\PrintSettingsQuery;
use App\Utility\PdfBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PrintDocumentController extends Controller
{
    /**
     * The preview page for one document.
     *
     * @param  string  $document
     * @return \Illuminate\View\View
     */
    public function show($document)
    {
        $this->authorize('print.viewAny');

        return view('admin.print.show', [
            'document' => $this->resolve($document),
        ]);
    }

    /**
     * Build one document and hand it back inline, or as a download.
     *
     * The document's own builder validates the parameters the print form
     * collected, so a document that is missing something it needs comes back as
     * that error — shown in the preview frame, which is where the form is —
     * rather than as a half-empty permit.
     *
     * Printing is not an activity the office records: the audit trail holds
     * what was changed, and a sheet of paper changes nothing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $document
     * @param  \App\Services\PrintDocuments\PrintDocumentService  $service
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pdf(
        Request $request,
        $document,
        PrintDocumentService $service
    ) {
        $this->authorize($request->query('download') ? 'print.export' : 'print.viewAny');

        $document = $this->resolve($document);

        $letterhead = app(PrintSettingsQuery::class)->letterhead();
        $params = $request->query();

        try {
            if ($request->query('blank')) {
                $data = $service->blank($document, $letterhead);
            } else {
                // A document opened from a homeowner's request is drawn from
                // that record: the request's own details fill the sheet before
                // the preview loads, and anything the officer typed on the
                // print form still wins for this print.
                $record = $this->requestedRecord($params['request'] ?? null);

                if ($record) {
                    $issued = $record->printDocument();

                    if (! $issued || $issued->value !== $document->value) {
                        return $this->unprintable($document, [
                            'The selected request does not issue a ' . $document->label() . '.',
                        ]);
                    }

                    $params = array_merge($record->documentParams(), $params);
                    unset($params['request']);
                }

                $data = $service->build($document, $params, $letterhead);
            }
        } catch (ValidationException $e) {
            return $this->unprintable($document, $this->messages($e));
        } catch (MissingDocumentDataException $e) {
            return $this->unprintable($document, $this->missingMessages($e));
        }

        $paper = $data['letterhead']['paper'] ?? $document->paper();
        $orientation = $data['orientation'] ?? $service->orientation($document);
        $filename = PdfBuilder::filename($document->label() . '-' . now()->format('Y-m-d'));

        if ($request->query('download')) {
            return PdfBuilder::download($document->view(), $data, $filename, $paper, $orientation);
        }

        return PdfBuilder::stream($document->view(), $data, $filename, $paper, $orientation);
    }

    /**
     * What a document that cannot be drawn yet answers with.
     *
     * The preview frame is an iframe pointed at this route, so the errors are
     * rendered as the page itself: redirecting back would load the whole print
     * page inside a panel designed to hold a sheet of paper.
     *
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function unprintable(PrintDocument $document, array $messages)
    {
        return response()->view('admin.print.invalid', [
            'document' => $document,
            'messages' => $messages,
        ], 422);
    }

    /**
     * The validation errors, as the preview frame lists them.
     *
     * @return array<int, string>
     */
    private function messages(ValidationException $exception): array
    {
        $messages = [];

        foreach ($exception->errors() as $fieldMessages) {
            foreach ($fieldMessages as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * What the document still needs, named the way the officer reads it.
     *
     * @return array<int, string>
     */
    private function missingMessages(MissingDocumentDataException $exception): array
    {
        $items = array_map(function (string $key) {
            return ucfirst(str_replace('_', ' ', $key));
        }, $exception->missing());

        return [__('documents.errors.missing', ['items' => implode(', ', $items)])];
    }

    /**
     * The homeowner request the page was opened from, or null for none.
     *
     * A missing or unknown id is left alone rather than refusing the print: the
     * officer can still print the sheet by hand. A request that exists is read
     * only through the Requests policy, the same permission the queue uses.
     *
     * @param  mixed  $id
     */
    private function requestedRecord($id): ?RequestModel
    {
        if (! is_numeric($id)) {
            return null;
        }

        $record = RequestModel::find((int) $id);

        if (! $record) {
            return null;
        }

        $this->authorize('view', RequestModel::class);

        return $record;
    }

    /**
     * The document the slug names, or a 404 for a slug the enum does not carry.
     *
     * @param  string  $document
     * @return \App\Enums\PrintDocument
     */
    private function resolve($document): PrintDocument
    {
        $resolved = PrintDocument::tryFrom((string) $document);

        abort_if($resolved === null, 404);

        return $resolved;
    }
}
