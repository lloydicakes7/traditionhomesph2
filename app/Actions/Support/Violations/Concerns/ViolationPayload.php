<?php

namespace App\Actions\Support\Violations\Concerns;

use App\Enums\ViolationType;

trait ViolationPayload
{
    // ===== Payload =====

    protected function payload(array $data): array
    {
        $typeOther = $data['violation_type'] === ViolationType::OTHER
            ? $this->nullIfBlank($data['violation_type_other'] ?? null)
            : null;

        return [
            'violation_type'       => $data['violation_type'],
            'violation_type_other' => $typeOther,
            'occurred_on'          => $data['occurred_on'],
            'occurred_time'        => ($data['occurred_time'] ?? null) ?: null,
            'location'             => $data['location'],
            'description'          => $data['description'],
            'office_report'        => $this->nullIfBlank($data['office_report'] ?? null),
        ];
    }

    // ===== Helpers =====

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
