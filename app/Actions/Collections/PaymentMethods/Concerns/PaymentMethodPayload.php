<?php

namespace App\Actions\Collections\PaymentMethods\Concerns;

trait PaymentMethodPayload
{
    // ===== Payload =====

    private function payload(array $data): array
    {
        return [
            'name'      => $data['name'],
            'details'   => ($data['details'] ?? null) ?: null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
