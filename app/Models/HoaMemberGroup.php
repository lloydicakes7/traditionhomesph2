<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoaMemberGroup extends Model
{
    const EXECUTIVE = 'THHA Executive Officers';

    const STAFF = 'Staff';

    const BOARD = 'Board of Directors';

    const COMMITTEE = 'Committee';

    const DISPLAY_ORDER = [
        self::EXECUTIVE,
        self::STAFF,
        self::BOARD,
        self::COMMITTEE,
    ];

    protected $fillable = [
        'name',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function members(): HasMany
    {
        return $this->hasMany(HoaMember::class, 'hoa_member_group_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeOrdered($query)
    {
        $whens = [];
        $bindings = [];

        foreach (array_values(self::DISPLAY_ORDER) as $rank => $name) {
            $whens[] = 'WHEN ? THEN ' . $rank;
            $bindings[] = strtolower($name);
        }

        return $query
            ->orderByRaw(
                'CASE LOWER(name) ' . implode(' ', $whens) . ' ELSE ' . count(self::DISPLAY_ORDER) . ' END',
                $bindings
            )
            ->orderBy('name')
            ->orderBy('id');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    public function isExecutive(): bool
    {
        return strcasecmp($this->name, self::EXECUTIVE) === 0
            || strcasecmp($this->name, self::STAFF) === 0;
    }

    public function isBoard(): bool
    {
        return strcasecmp($this->name, self::BOARD) === 0;
    }

    public function isCommittee(): bool
    {
        return strcasecmp($this->name, self::COMMITTEE) === 0;
    }
}
