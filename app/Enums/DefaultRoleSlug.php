<?php

namespace App\Enums;

/**
 * The roles THHA ships with — the ones the system itself knows by name.
 *
 * @method static DefaultRoleSlug ADMINISTRATOR()
 * @method static DefaultRoleSlug VISITOR()
 */
class DefaultRoleSlug extends Enum
{
    const ADMINISTRATOR = 'Administrator';
    const VISITOR       = 'Visitor';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ADMINISTRATOR => 'Administrator',
            self::VISITOR       => 'Visitor',
        ];
    }

    /**
     * Map of value → what the role is for. This is the copy the roles are
     * seeded with, so an install's own wording comes from one place.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::ADMINISTRATOR => 'Super admin with full system access',
            self::VISITOR       => 'Guest user with limited access',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * What this role is for.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * Whether the software itself runs on this role — it holds every ability
     * and cannot be taken away from an install without breaking it. The roles
     * list badges these as "System".
     */
    public function isSystem(): bool
    {
        return self::isSystemSlug($this->value);
    }

    /**
     * The roles the software runs on, by slug.
     *
     * @return array<int, string>
     */
    public static function systemSlugs(): array
    {
        return [self::ADMINISTRATOR, self::VISITOR];
    }

    /**
     * Whether a role name is one the software runs on.
     */
    public static function isSystemSlug(?string $slug): bool
    {
        return in_array($slug, self::systemSlugs(), true);
    }

    /**
     * The roles that are the catalogue itself: the association's super admin
     * holds everything the panel declares, whether or not any of it is written
     * on its row, and the office is never offered a switch for it.
     *
     * @return array<int, string>
     */
    public static function fullAccessSlugs(): array
    {
        return [self::ADMINISTRATOR];
    }

    /**
     * Whether a role name always holds the whole catalogue.
     */
    public static function isFullAccessSlug(?string $slug): bool
    {
        return in_array($slug, self::fullAccessSlugs(), true);
    }

    /**
     * Whether this role always holds the whole catalogue.
     */
    public function hasFullAccess(): bool
    {
        return self::isFullAccessSlug($this->value);
    }

    /**
     * Whether a role name is one THHA ships. A shipped role is the
     * association's own standing role — seeded, referenced by the app and
     * never deleted, only reassigned.
     */
    public static function isProtectedSlug(?string $slug): bool
    {
        return in_array($slug, self::values(), true);
    }

    /**
     * AdminLTE badge theme for this role — the system roles read as the
     * association's own, the officer roles as the office's.
     */
    public function badgeTheme(): string
    {
        return $this->isSystem() ? 'info' : 'secondary';
    }
}
