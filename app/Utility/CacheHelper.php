<?php

namespace App\Utility;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function remember(string $group, $key, int $ttl, Closure $callback)
    {
        return Cache::remember(self::key($group, $key), $ttl, $callback);
    }

    public static function flush(string $group): void
    {
        Cache::forever(self::versionKey($group), self::version($group) + 1);
    }

    private static function key(string $group, $key): string
    {
        return 'thha.' . $group . '.v' . self::version($group) . '.' . md5((string) json_encode($key));
    }

    private static function version(string $group): int
    {
        return (int) Cache::get(self::versionKey($group), 1);
    }

    private static function versionKey(string $group): string
    {
        return 'thha.version.' . $group;
    }
}
