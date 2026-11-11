<?php

namespace App\Models;

use App\Enums\DefaultRoleSlug;
use App\Utility\PermissionHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An officer role — the office's own title for a set of things it may do.
 *
 * The role owns the permissions it was granted as keys on itself
 * (`roles.permissions`, e.g. `addresses.view`), so a permission check is a
 * lookup rather than a join; what a key can be is the catalogue's business
 * (App\Utility\PermissionHelper), and this model never invents one. A role that
 * *is* the catalogue — the Administrator (`DefaultRoleSlug::isFullAccessSlug()`)
 * — holds every key without storing any, so a module declared in code is granted
 * to it the moment it exists.
 *
 * Whether a role is one THHA ships — and so may be staffed but not deleted —
 * is not this table's business either: it is answered by the typed role list in
 * `App\Enums\DefaultRoleSlug`.
 */
class Role extends Model
{
    protected $fillable = ['name', 'description', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * The permission keys already resolved for this row, read once per instance.
     *
     * Permission checks are the hottest read in the panel — every Policy, every
     * menu item and every `@can` asks the role what it holds — and resolving the
     * keys walks the whole catalogue. Doing that once per request is what keeps a
     * page with dozens of checks off the database and out of the catalogue.
     *
     * @var array<int, string>|null
     */
    private $resolvedPermissionKeys;

    /**
     * The same keys flipped for an O(1) lookup in `hasPermission()`.
     *
     * @var array<string, bool>|null
     */
    private $permissionKeyIndex;

    /**
     * Get the admins holding this role.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    /**
     * The permission keys this role holds.
     *
     * A role that is the catalogue itself (`DefaultRoleSlug::isFullAccessSlug()` —
     * the Administrator) holds every key the catalogue declares, so its row need
     * not store a single one and a new module is granted to it the moment it is
     * declared in code. Every other role is read from its own stored keys, and
     * only keys the catalogue still declares are kept: a key left behind by a
     * retired module grants nothing and is not a permission this role has.
     *
     * @return array<int, string>
     */
    public function permissionKeys(): array
    {
        if ($this->resolvedPermissionKeys !== null) {
            return $this->resolvedPermissionKeys;
        }

        if ($this->hasFullAccess()) {
            return $this->resolvedPermissionKeys = PermissionHelper::keys();
        }

        $keys = array_filter((array) $this->permissions, function ($key) {
            return is_string($key) && PermissionHelper::isValid($key);
        });

        return $this->resolvedPermissionKeys = array_values(array_unique($keys));
    }

    /**
     * Whether this role may take an action on a module.
     *
     * Every Policy asks this way — `$role->hasPermission(self::MODULE, 'View')`
     * — so what a role can do is stated once, in its own keys. The lookup is a
     * keyed one because permission checks are the hottest read in the panel.
     */
    public function hasPermission(string $module, string $type): bool
    {
        if ($this->hasFullAccess()) {
            return true;
        }

        if ($this->permissionKeyIndex === null) {
            $this->permissionKeyIndex = array_fill_keys($this->permissionKeys(), true);
        }

        return isset($this->permissionKeyIndex[PermissionHelper::key($module, $type)]);
    }

    /**
     * Whether this role is the catalogue itself — the permissions on its row
     * are not the office's to edit, because the software decides them.
     */
    public function hasFullAccess(): bool
    {
        return DefaultRoleSlug::isFullAccessSlug($this->name);
    }

    /**
     * How many permissions this role holds — what the roles list counts in a
     * column, without a second query for it.
     */
    public function getPermissionsCountAttribute(): int
    {
        return count($this->permissionKeys());
    }

    /**
     * Whether the software itself runs on this role (Administrator, Visitor).
     */
    public function getIsSystemAttribute(): bool
    {
        return DefaultRoleSlug::isSystemSlug($this->name);
    }

    /**
     * Whether THHA ships this role — its standing roles, which are reassigned
     * rather than deleted.
     */
    public function getIsProtectedAttribute(): bool
    {
        return DefaultRoleSlug::isProtectedSlug($this->name);
    }

    /**
     * Why this role may not be deleted, or null while it may be.
     *
     * The rule lives here and nowhere else: the roles list refuses to open the
     * delete dialog with this reason, and deleting a role re-checks it before
     * anything is removed — a role THHA ships (`DefaultRoleSlug`) is reassigned,
     * never removed.
     */
    public function deletionBlocker(): ?string
    {
        if ($this->is_protected) {
            return 'This is one of the association\'s own roles — change what it may do instead of deleting it.';
        }

        if ($this->admins()->exists()) {
            return 'This role still has users assigned to it. Reassign them first.';
        }

        return null;
    }

    /**
     * The AdminLTE badge theme this role's name reads in — a role the software
     * runs on as the association's own, an office-created one as the office's.
     * Stated here (from the enum) so no view decides it for itself.
     */
    public function getBadgeThemeAttribute(): string
    {
        $slug = DefaultRoleSlug::tryFrom($this->name);

        return $slug ? $slug->badgeTheme() : 'secondary';
    }
}
