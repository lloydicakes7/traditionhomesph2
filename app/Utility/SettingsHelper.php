<?php

namespace App\Utility;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Generic, domain-agnostic helper for reading/writing settings from the
 * database settings table and managing their cache.
 *
 * This class wraps low-level technical operations (DB reads/writes, cache
 * invalidation) so that Services and Livewire components never call
 * Setting::updateOrCreate() or Cache::forget() directly with the same
 * arguments in multiple places.
 *
 * Reads are cached forever per settings group (see cacheKey()) and the cache
 * is invalidated by flush() — which the write methods below call themselves,
 * so a saved setting is never served stale. The cache entry is keyed by the
 * group ("settings.{group}"), so every service that owns a group shares the
 * one entry with the writes that flush it.
 *
 * It has no knowledge of business rules, default values, or type coercion —
 * those belong in the Service layer (e.g. GeneralSettingsQuery, SecuritySettingsQuery)
 * or in the Livewire component itself.
 *
 * @see \App\Queries\Settings\GeneralSettingsQuery
 * @see \App\Queries\Settings\SecuritySettingsQuery
 */
class SettingsHelper
{
    private static $settingsTableExists = null;

    /**
     * Groups already read during this request, keyed by group.
     *
     * The persistent cache is the source of truth across requests, but a page
     * rendering dozens of views asks for the same group dozens of times — the
     * HoaInfoComposer runs for every view in the tree. Reading the cache once per
     * group per request is what keeps that off the cache store on every partial.
     *
     * @var array<string, array<string, string>>
     */
    private static $memo = [];

    /**
     * Retrieve all settings from a group as a key-value array.
     *
     * @param  string  $group  The settings group (e.g. 'hoa_info', 'security').
     * @return array<string, string>
     */
    public static function getByGroup(string $group): array
    {
        if (array_key_exists($group, static::$memo)) {
            return static::$memo[$group];
        }

        if (! static::hasSettingsTable()) {
            return [];
        }

        return static::$memo[$group] = Cache::rememberForever(static::cacheKey($group), function () use ($group) {
            return Setting::group($group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Retrieve a single setting value by key and group.
     *
     * @param  string  $key     The setting key.
     * @param  string  $group   The settings group.
     * @param  mixed   $default Value to return if the setting is not found.
     * @return string|null
     */
    public static function get(string $key, string $group, $default = null): ?string
    {
        return static::getByGroup($group)[$key] ?? $default;
    }

    /**
     * Update or create a single setting.
     *
     * @param  string  $key    The setting key.
     * @param  string  $value  The value to store.
     * @param  string  $group  The settings group.
     * @return void
     */
    public static function set(string $key, string $value, string $group): void
    {
        Setting::updateOrCreate(
            ['key' => $key, 'group' => $group],
            ['value' => $value]
        );

        static::flush($group);
    }

    /**
     * Update or create multiple settings within the same group.
     *
     * @param  array<string, string>  $data   Key-value pairs of settings.
     * @param  string                 $group  The settings group.
     * @return void
     */
    public static function bulkSet(array $data, string $group): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => $group],
                ['value' => $value]
            );
        }

        static::flush($group);
    }

    /**
     * Delete a single setting by key and group.
     *
     * @param  string  $key    The setting key to delete.
     * @param  string  $group  The settings group.
     * @return void
     */
    public static function delete(string $key, string $group): void
    {
        Setting::where('key', $key)
            ->where('group', $group)
            ->delete();

        static::flush($group);
    }

    /**
     * Invalidate the cached settings of a group.
     *
     * Called by the write methods above whenever a setting is saved, and
     * available to callers that need to force a re-read.
     *
     * @param  string  $group  The settings group to invalidate.
     * @return void
     */
    public static function flush(string $group): void
    {
        unset(static::$memo[$group]);
        Cache::forget(static::cacheKey($group));
    }

    /**
     * Invalidate a cache key.
     *
     * @param  string  $key  The full cache key to forget.
     * @return void
     */
    public static function forgetCache(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Cache key holding one settings group as a key-value array.
     */
    private static function cacheKey(string $group): string
    {
        return 'settings.' . $group;
    }

    /**
     * Whether the settings table exists.
     *
     * A "not yet" answer is not memoized — that is what lets a process which
     * booted before the migrations ran (a fresh install, a test run) start
     * reading settings as soon as the table is there. Once the table has been
     * seen, the check is not repeated.
     */
    private static function hasSettingsTable(): bool
    {
        if (static::$settingsTableExists === null) {
            static::$settingsTableExists = Schema::hasTable('settings') ?: null;
        }

        return static::$settingsTableExists === true;
    }
}
