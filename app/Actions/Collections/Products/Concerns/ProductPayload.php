<?php

namespace App\Actions\Collections\Products\Concerns;

trait ProductPayload
{
    // ===== Payload =====

    private function payload(array $data): array
    {
        return [
            'product_category_id' => ($data['product_category_id'] ?? null) ?: null,
            'name'                => $data['name'],
            'type'                => $data['type'],
            'price'               => $data['price'] ?? 0,
            'unit'                => ($data['unit'] ?? null) ?: null,
            'description'         => ($data['description'] ?? null) ?: null,
            'is_active'           => (bool) ($data['is_active'] ?? false),
        ];
    }
}
