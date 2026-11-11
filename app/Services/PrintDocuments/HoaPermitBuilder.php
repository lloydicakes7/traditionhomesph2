<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Queries\Documents\HoaPermitDocumentQuery;

/**
 * The Homeowners Association Permit — clearance for work or an activity inside
 * the subdivision.
 *
 * The parameters are validated against the document's own rules, then handed to
 * the document query, which owns the paper's data contract.
 */
final class HoaPermitBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\HoaPermitDocumentQuery */
    private $query;

    public function __construct(
        \App\Enums\PrintDocument $document,
        HoaPermitDocumentQuery $query
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
