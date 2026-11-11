<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

/**
 * One association document, drawn from the parameters its print form collected.
 *
 * Every printable document has its own builder, and a builder owns everything
 * about that document: the parameters it needs, the records it reads, and the
 * view-model it hands the Blade view. `PrintDocumentService` does nothing but
 * pick the right builder and merge the chrome every document shares, so there
 * is no place left where one document's keys can leak into another's.
 *
 * The view-model a builder returns is plain arrays and strings — no models, no
 * Collections, no enums — because a view formats what it is given and reads
 * nothing else. The view in `resources/views/pdf/` is written against that
 * view-model key for key, so a builder and its view always change together,
 * and the view reads exactly the keys the builder returns.
 */
interface PrintDocumentBuilder
{
    /**
     * The document's view-model, built from the parameters its form collected.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException when a required parameter is missing
     */
    public function build(array $params): array;

    /**
     * The orientation the document is laid out in — "portrait" for every
     * document but the residents list, which runs across the sheet.
     */
    public function orientation(): string;
}
