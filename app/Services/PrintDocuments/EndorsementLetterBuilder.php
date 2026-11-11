<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Queries\Documents\EndorsementLetterDocumentQuery;

/**
 * The Endorsement Letter — the association endorsing a homeowner's complaint to
 * the barangay.
 *
 * The parameters are validated against the document's own rules, then handed to
 * the document query, which owns the paper's data contract.
 */
final class EndorsementLetterBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\EndorsementLetterDocumentQuery */
    private $query;

    public function __construct(
        \App\Enums\PrintDocument $document,
        EndorsementLetterDocumentQuery $query
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
