<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;

class DirectoryEntry extends Model
{
    protected $fillable = [
        'directory_group_id',
        'name',
        'role',
        'email',
        'note',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function group()
    {
        return $this->belongsTo(DirectoryGroup::class, 'directory_group_id');
    }

    public function phones()
    {
        return $this->hasMany(DirectoryPhone::class, 'directory_entry_id')->orderBy('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    public function getPhoneListAttribute(): string
    {
        $numbers = $this->phones->pluck('number')->filter()->all();

        return implode(' · ', $numbers);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)
                ->orWhere('role', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('note', 'like', $like)
                ->orWhereHas('group', function ($group) use ($like) {
                    $group->where('name', 'like', $like);
                })
                ->orWhereHas('phones', function ($phone) use ($like) {
                    $phone->where('number', 'like', $like)
                        ->orWhere('label', 'like', $like);
                });
        });
    }
}
