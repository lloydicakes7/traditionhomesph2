<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;

class DirectoryGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function entries()
    {
        return $this->hasMany(DirectoryEntry::class, 'directory_group_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
