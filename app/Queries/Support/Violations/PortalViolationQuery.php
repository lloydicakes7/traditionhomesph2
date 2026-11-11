<?php

namespace App\Queries\Support\Violations;

use App\Models\Violation;

class PortalViolationQuery
{
    // ===== Rows =====

    /**
     * One resident's notices, split the way their page reads them: what still
     * stands ("active") and what the office has closed ("history").
     *
     * Both lists come from one scoped query, so another household's violations
     * are never fetched and then filtered away.
     *
     * @return array{active: \Illuminate\Database\Eloquent\Collection, history: \Illuminate\Database\Eloquent\Collection}
     */
    public function forResident(?int $residentId): array
    {
        $violations = Violation::query()
            ->forResident($residentId)
            ->with('issuedByAdmin')
            ->ordered()
            ->get();

        return [
            'active'  => $violations->filter(function (Violation $violation) {
                return $violation->status && $violation->status->isActive();
            })->values(),
            'history' => $violations->filter(function (Violation $violation) {
                return ! $violation->status || $violation->status->isResolved();
            })->values(),
        ];
    }
}
