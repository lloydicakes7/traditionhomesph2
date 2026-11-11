<?php

namespace App\Actions\HoaCenter\Announcements\Concerns;

use App\Utility\StorageHelper;

trait AnnouncementPayload
{
    // ===== Payload =====

    protected function payload(array $data): array
    {
        return [
            'title' => $data['title'],
            'body'  => trim(preg_replace('/[ \t]+$/m', '', $data['body'])),
        ];
    }

    // ===== Image =====

    protected function imageDirectory(): string
    {
        return 'photos/announcements';
    }

    protected function storeImage($image): string
    {
        return StorageHelper::storePhoto($image, $this->imageDirectory());
    }

    protected function replaceImage($image, ?string $current): string
    {
        return StorageHelper::replacePhoto($image, $current, $this->imageDirectory());
    }

    protected function deleteImage(?string $path): void
    {
        StorageHelper::deletePhoto($path);
    }
}
