<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Queries\Documents\CertificationDocumentQuery;

/**
 * The Certification — the association certifying who occupies a unit and for
 * what purpose the certificate is being asked.
 *
 * The parameters are validated against the document's own rules, then handed to
 * the document query, which owns the paper's data contract. The view draws what
 * the query answers and decides nothing about whether a field prints.
 */
final class CertificationBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\CertificationDocumentQuery */
    private $query;

    public function __construct(
        PrintDocument $document,
        CertificationDocumentQuery $query
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
