<?php

namespace App\Models;

use App\Utility\HtmlHelper;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
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
            $query->where('question', 'like', $like)
                ->orWhere('answer', 'like', $like);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    // ===== Accessors =====

    public function getAnswerHtmlAttribute(): string
    {
        return HtmlHelper::toParagraphs((string) $this->answer);
    }

    // ===== Helpers =====

    public static function payloadFrom(array $data): array
    {
        return [
            'question' => trim($data['question']),
            'answer'   => trim($data['answer']),
        ];
    }
}
