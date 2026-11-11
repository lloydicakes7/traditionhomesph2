<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Queries\Documents\BusinessPermitDocumentQuery;

/**
 * The Business Permit — a framed paper form issued to a homeowner for a
 * home-based business or a utility connection.
 *
 * What the permit is *for* is its own tick-list, and it is the one thing the
 * officer must choose. The parameters are validated against the document's own
 * rules, then handed to the document query, which owns the paper's data
 * contract.
 */
final class BusinessPermitBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\BusinessPermitDocumentQuery */
    private $query;

    public function __construct(
        PrintDocument $document,
        BusinessPermitDocumentQuery $query
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
