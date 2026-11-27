<?php

namespace App\Services\Access;

use App\Enums\DefaultRoleSlug;
use App\Models\Role;
use App\Utility\PermissionHelper;

/**
 * Keeps the roles the software runs on in the database.
 */
class DefaultRoleService
{
    const VISITOR_MODULES = [
        'Addresses',
        'Homeowners',
        'Residents',
        'Properties',
        'Households',
        'Directory',
        'Members',
        'Announcements',
        'Amenities',
        'Amenity Bookings',
        'Requests',
        'Violations',
        'Complaints',
    ];

    // ===== Operations =====

    /**
     * Create any role the software runs on that the database lost, with the permissions it must hold.
     */
    public function ensure(): void
    {
        foreach (DefaultRoleSlug::systemSlugs() as $slug) {
            $this->ensureRole($slug);
        }
    }

    /**
     * Return a role the software runs on, creating it when the database lost it.
     */
    public function role(string $slug): Role
    {
        return $this->ensureRole($slug);
    }

    // ===== Helpers =====

    private function ensureRole(string $slug): Role
    {
        $role = Role::firstOrCreate(
            ['name' => $slug],
            ['description' => DefaultRoleSlug::from($slug)->description()]
        );

        if ($slug === DefaultRoleSlug::VISITOR && $role->permissions === null) {
            $role->update(['permissions' => $this->viewKeys()]);
        }

        return $role;
    }

    /**
     * The permission keys the Visitor reads the association's records with.
     *
     * @return array<int, string>
     */
    private function viewKeys(): array
    {
        $keys = [];

        foreach (self::VISITOR_MODULES as $module) {
            if (in_array('View', PermissionHelper::typesFor($module), true)) {
                $keys[] = PermissionHelper::key($module, 'View');
            }
        }

        return $keys;
    }
}
