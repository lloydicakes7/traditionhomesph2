<?php

namespace App\Actions\UserManagement\Roles\Concerns;

use App\Enums\DefaultRoleSlug;

trait RolePermissions
{
    // ===== Payload =====

    /**
     * What a role's `permissions` column is written as.
     *
     * A role that is the catalogue itself holds every key by declaration, so
     * nothing is stored against it — which is what lets a module added to the
     * catalogue tomorrow be granted to it without a migration, a seeder or an
     * officer's visit to the roles page.
     */
    protected function storedPermissions(?string $name, array $permissionKeys): array
    {
        return DefaultRoleSlug::isFullAccessSlug($name) ? [] : array_values($permissionKeys);
    }
}
