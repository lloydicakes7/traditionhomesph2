<?php

namespace App\Models;

use App\Utility\HtmlHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Content extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'group',
        'image_path',
        'details',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ===== Scopes =====

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('title', 'like', $like)
                ->orWhere('group', 'like', $like)
                ->orWhere('details', 'like', $like);
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('title');
    }

    // ===== Accessors =====

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function getDetailsHtmlAttribute(): string
    {
        $html = HtmlHelper::sanitize($this->details);

        return HtmlHelper::isPlainText($html) ? HtmlHelper::toParagraphs($html) : $html;
    }

    public function getUrlAttribute(): string
    {
        return route('content.show', $this->slug);
    }

    // ===== Helpers =====

    public static function payloadFrom(array $data): array
    {
        return [
            'title'        => trim($data['title']),
            'group'        => ($data['group'] ?? null) ?: null,
            'details'      => HtmlHelper::sanitize($data['details']),
            'is_published' => array_key_exists('is_published', $data) ? (bool) $data['is_published'] : true,
        ];
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
