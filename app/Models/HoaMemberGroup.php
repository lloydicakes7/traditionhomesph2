<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoaMemberGroup extends Model
{
    const STAFF = 'Staff';

    const BOARD = 'Board of Directors';

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
        return $query->orderBy('name');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    public function isBoard(): bool
    {
        return strcasecmp($this->name, self::BOARD) === 0;
    }
}
