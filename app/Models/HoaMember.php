<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HoaMember extends Model
{

    protected $fillable = [
        'hoa_member_group_id',
        'name',
        'position',
        'phone',
        'email',
        'note',
        'profile_photo_path',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function group()
    {
        return $this->belongsTo(HoaMemberGroup::class, 'hoa_member_group_id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null;
    }

    public function getDialablePhoneAttribute(): ?string
    {
        if ($this->phone === null || $this->phone === '') {
            return null;
        }

        return preg_replace('/[^\d+]/', '', (string) $this->phone);
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
                ->orWhere('position', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('note', 'like', $like);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at')->orderBy('id');
    }

    public function scopeInPositionOrder(Builder $query, array $positions): Builder
    {
        if ($positions === []) {
            return $query->ordered();
        }

        $whens = [];
        $bindings = [];

        foreach ($positions as $position => $rank) {
            $whens[] = 'WHEN ? THEN ' . (int) $rank;
            $bindings[] = strtoupper($position);
        }

        return $query
            ->orderByRaw('CASE UPPER(TRIM(position)) ' . implode(' ', $whens) . ' ELSE 999 END', $bindings)
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
