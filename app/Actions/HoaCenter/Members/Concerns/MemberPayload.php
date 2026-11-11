<?php

namespace App\Actions\HoaCenter\Members\Concerns;

use App\Utility\StorageHelper;

trait MemberPayload
{
    protected string $photoDirectory = 'photos/members';

    // ===== Payload =====

    protected function payload(array $data): array
    {
        return [
            'name'     => $data['name'],
            'position' => $data['position'],
            'phone'    => $this->nullIfBlank($data['phone'] ?? null),
            'email'    => $this->nullIfBlank($data['email'] ?? null),
            'note'     => $this->nullIfBlank($data['note'] ?? null),
        ];
    }

    // ===== Photo =====

    protected function storePhoto($photo): string
    {
        return StorageHelper::storePhoto($photo, $this->photoDirectory);
    }

    protected function replacePhoto($photo, ?string $current): string
    {
        return StorageHelper::replacePhoto($photo, $current, $this->photoDirectory);
    }

    protected function deletePhoto(?string $path): void
    {
        StorageHelper::deletePhoto($path);
    }

    // ===== Helpers =====

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
