<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    protected $casts = [
        'value' => 'string',
    ];

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }
}
