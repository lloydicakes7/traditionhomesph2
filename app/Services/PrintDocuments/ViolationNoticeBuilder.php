<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Models\Violation;
use App\Utility\PrintDocumentsHelper as Helper;

/**
 * The Notice of Violation — what was recorded against a homeowner, where and
 * when, and what the office found.
 *
 * The notice is read from the violation record itself, so
 * what the homeowner is handed and what the office reads on screen are the same
 * record. A notice that has not been issued, or that carries no office report
 * or resolution, prints without those sections rather than with empty ones.
 */
final class ViolationNoticeBuilder extends AbstractPrintDocumentBuilder
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $violation = Violation::with(['resident', 'issuedByAdmin', 'resolvedByAdmin'])
            ->find((int) $this->intOrNull($params, 'violationId'));

        return ['violation' => $violation ? $this->notice($violation) : null];
    }

    /**
     * The violation as the notice prints it.
     *
     * @return array<string, mixed>
     */
    private function notice(Violation $violation): array
    {
        return [
            'homeowner'      => $this->homeowner($violation),
            'status'         => (string) $violation->status_label,
            'type'           => (string) $violation->type_label,
            'occurredAt'     => Helper::stamp($violation->occurred_on, $violation->occurred_time),
            'location'       => trim((string) $violation->location) !== '' ? (string) $violation->location : '—',
            'issuedAt'       => $violation->issued_at ? Helper::stamp($violation->issued_at) : '—',
            'issuedBy'       => $violation->issuedByAdmin ? (string) $violation->issuedByAdmin->name : '—',
            'acknowledgment' => $violation->acknowledged_at ? Helper::stamp($violation->acknowledged_at) : 'Pending',
            'description'    => (string) $violation->description,
            'officeReport'   => $violation->has_office_report ? (string) $violation->office_report : null,
            'resolution'     => $this->resolution($violation),
        ];
    }

    /**
     * Who the notice is against: the resident on the office's records, and the
     * unit they hold. A resident the office has since removed leaves the name
     * the citation was issued under, so the notice stays readable.
     */
    private function homeowner(Violation $violation): string
    {
        $property = $violation->resident ? $violation->resident->unit() : null;

        if (! $violation->resident) {
            return (string) $violation->resident_name;
        }

        $name = Helper::displayName($violation->resident);

        return $property ? $name . ' · ' . $property->block_lot_label : $name;
    }

    /**
     * How the violation was closed, when the office has closed it.
     *
     * @return array<string, string>|null
     */
    private function resolution(Violation $violation): ?array
    {
        if (! $violation->resolved_at) {
            return null;
        }

        return [
            'when'  => Helper::stamp($violation->resolved_at),
            'by'    => $violation->resolvedByAdmin ? (string) $violation->resolvedByAdmin->name : '',
            'notes' => trim((string) $violation->resolution_notes),
        ];
    }
}
