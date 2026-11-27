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

    const EXECUTIVE_NAMES = [
        self::EXECUTIVE,
        self::STAFF,
        'Executive Officer',
        'Executive Officers',
    ];

    const FIXED_ORDER = [
        self::EXECUTIVE      => 0,
        self::STAFF          => 0,
        'Executive Officers' => 0,
        'Executive Officer'  => 0,
        self::BOARD          => 1,
        self::COMMITTEE      => 2,
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

        foreach (self::FIXED_ORDER as $name => $rank) {
            $whens[] = 'WHEN ? THEN ' . (int) $rank;
            $bindings[] = strtolower($name);
        }

        return $query
            ->orderByRaw(
                'CASE LOWER(TRIM(name)) ' . implode(' ', $whens) . ' ELSE ' . count(self::FIXED_ORDER) . ' END',
                $bindings
            )
            ->orderBy('created_at')
            ->orderBy('id');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    public function isExecutive(): bool
    {
        $name = strtolower(trim((string) $this->name));

        foreach (self::EXECUTIVE_NAMES as $executive) {
            if ($name === strtolower($executive)) {
                return true;
            }
        }

        return false;
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
