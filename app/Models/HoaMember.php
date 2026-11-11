<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HoaMember extends Model
{

    protected $fillable = [
        'name',
        'position',
        'phone',
        'email',
        'note',
        'profile_photo_path',
    ];

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
        return $query->orderBy('name');
    }
}
