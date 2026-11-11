<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Queries\Documents\SinumpaangSalaysayDocumentQuery;

/**
 * The Sinumpaang Salaysay — a sworn statement, in Filipino, of what happened
 * between two homeowners.
 *
 * The affiant states it in their own words, and the office types those words in
 * as they were given. The statement is written onto ruled lines by hand, so the
 * narrative is wrapped to the width of a line and the area is padded out to the
 * form's full height; what is left is ruled and empty.
 *
 * It is the one paper form with no officer sidebar: the statement is the
 * affiant's, and the two people who sign it — the committee chair and the
 * president — sign at the foot. The parameters are validated against the
 * document's own rules, then handed to the document query.
 */
final class SinumpaangSalaysayBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\SinumpaangSalaysayDocumentQuery */
    private $query;

    public function __construct(
        PrintDocument $document,
        SinumpaangSalaysayDocumentQuery $query
    ) {
        parent::__construct($document);

        $this->query = $query;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        return $this->query->build($params);
    }

    /**
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return $this->query->blank();
    }
}
