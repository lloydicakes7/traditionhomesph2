<?php

namespace App\Actions\HoaCenter\Members\Concerns;

use App\Models\HoaMemberGroup;
use App\Utility\StorageHelper;
use Illuminate\Validation\ValidationException;

trait MemberPayload
{
    protected string $photoDirectory = 'photos/members';

    // ===== Payload =====

    protected function payload(array $data, HoaMemberGroup $group): array
    {
        return [
            'hoa_member_group_id' => $group->id,
            'name'                => $data['name'],
            'position'            => $data['position'],
            'phone'               => $this->nullIfBlank($data['phone'] ?? null),
            'email'               => $this->nullIfBlank($data['email'] ?? null),
            'note'                => $this->nullIfBlank($data['note'] ?? null),
        ];
    }

    // ===== Group =====

    protected function requireGroup($groupId, ?string $newName): HoaMemberGroup
    {
        $group = $this->resolveGroup($groupId, $newName);

        if ($group) {
            return $group;
        }

        throw ValidationException::withMessages([
            'form.hoa_member_group_id' => 'Select an existing group or enter a new group name.',
        ]);
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

    private function resolveGroup($groupId, ?string $newName): ?HoaMemberGroup
    {
        if (is_numeric($groupId) && $group = HoaMemberGroup::find($groupId)) {
            return $group;
        }

        $name = trim((string) $newName);

        return $name === '' ? null : HoaMemberGroup::firstOrCreate(['name' => $name]);
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
