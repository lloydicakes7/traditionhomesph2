<?php

namespace App\Queries\HoaCenter\Faqs;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FaqListQuery
{
    const PER_PAGE = 15;

    // ===== Queries =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Faq::query()
            ->search($search)
            ->ordered()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Faq::query()
            ->ordered()
            ->get(['id', 'question', 'answer']);
    }

    public function find(int $id): Faq
    {
        return Faq::findOrFail($id);
    }
}
