<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Queries\Documents\InvitationLetterDocumentQuery;

/**
 * The Invitation Letter — one framed sheet per household the association calls
 * to a meeting.
 *
 * The parameters are validated against the document's own rules, then handed to
 * the document query, which owns the paper's data contract.
 */
final class InvitationLetterBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\InvitationLetterDocumentQuery */
    private $query;

    public function __construct(
        \App\Enums\PrintDocument $document,
        InvitationLetterDocumentQuery $query
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
