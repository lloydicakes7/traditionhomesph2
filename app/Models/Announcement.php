<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // =====================================================================
    // Display
    // =====================================================================

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function getDisplayDateAttribute(): string
    {
        return $this->published_at
            ? $this->published_at->format('M j, Y')
            : $this->created_at->format('M j, Y');
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
            $query->where('title', 'like', $like)
                ->orWhere('body', 'like', $like);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('published_at')->orderByDesc('created_at');
    }
}
