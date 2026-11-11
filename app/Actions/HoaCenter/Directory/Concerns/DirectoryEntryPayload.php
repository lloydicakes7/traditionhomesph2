<?php

namespace App\Actions\HoaCenter\Directory\Concerns;

use App\Models\Directory\DirectoryGroup;
use Illuminate\Validation\ValidationException;

trait DirectoryEntryPayload
{
    // ===== Payload =====

    protected function payload(array $data, DirectoryGroup $group): array
    {
        return [
            'directory_group_id' => $group->id,
            'name'               => $data['name'],
            'role'               => $this->nullIfBlank($data['role'] ?? null),
            'email'              => $this->nullIfBlank($data['email'] ?? null),
            'note'               => $this->nullIfBlank($data['note'] ?? null),
        ];
    }

    protected function phoneRows(array $rows): array
    {
        $phones = [];

        foreach ($rows as $row) {
            $number = trim((string) ($row['number'] ?? ''));

            if ($number === '') {
                continue;
            }

            $phones[] = [
                'label'  => $this->nullIfBlank($row['label'] ?? null),
                'number' => $number,
            ];
        }

        return $phones;
    }

    // ===== Group =====

    protected function requireGroup($groupId, ?string $newName): DirectoryGroup
    {
        $group = $this->resolveGroup($groupId, $newName);

        if ($group) {
            return $group;
        }

        throw ValidationException::withMessages([
            'form.directory_group_id' => 'Select an existing group or enter a new group name.',
        ]);
    }

    // ===== Helpers =====

    private function resolveGroup($groupId, ?string $newName): ?DirectoryGroup
    {
        if (is_numeric($groupId) && $group = DirectoryGroup::find($groupId)) {
            return $group;
        }

        $name = trim((string) $newName);

        return $name === '' ? null : DirectoryGroup::firstOrCreate(['name' => $name]);
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
