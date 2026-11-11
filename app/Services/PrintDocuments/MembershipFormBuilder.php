<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Queries\Documents\MembershipFormDocumentQuery;

/**
 * The Membership Form — the association's paper enrolment form.
 *
 * The office may print it empty and fill it in at the desk, which is why a form
 * with no member chosen returns the blank paper rather than an error.
 */
final class MembershipFormBuilder extends AbstractPrintDocumentBuilder
{
    /** @var \App\Queries\Documents\MembershipFormDocumentQuery */
    private $query;

    public function __construct(
        \App\Enums\PrintDocument $document,
        MembershipFormDocumentQuery $query
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
        if (! is_numeric($params['residentId'] ?? null)) {
            return $this->query->blank();
        }

        return $this->query->preview($params);
    }

    /**
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return $this->query->blank();
    }
}
