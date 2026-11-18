<?php

namespace App\Queries\UserManagement\Activities;

use App\Models\AdminActivity;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminActivityListQuery
{
    const PER_PAGE = 15;

    /**
     * The activity trail, newest first, narrowed by the office's search and by
     * the day and time window it is read for.
     */
    public function paginate(array $filters = [], int $perPage = self::PER_PAGE, string $pageName = 'activityPage'): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->with('admin')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $pageName);
    }

    /**
     * The ids of every activity log the filters keep, for "select all pages".
     *
     * @return array<int, int>
     */
    public function ids(array $filters = []): array
    {
        $ids = $this->filtered($filters)->pluck('id')->all();

        return array_map('intval', $ids);
    }

    /**
     * The trail narrowed by the filters the page holds.
     */
    private function filtered(array $filters): Builder
    {
        $query = AdminActivity::query();

        $this->applySearch($query, trim((string) ($filters['search'] ?? '')));
        $this->applyWindow($query, $filters['date'] ?? null, $filters['from_time'] ?? null, $filters['to_time'] ?? null);

        return $query;
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $like = '%' . $search . '%';

        $query->where(function (Builder $activity) use ($like) {
            $activity->where('action', 'like', $like)
                ->orWhere('module', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('admin', function (Builder $admin) use ($like) {
                    $admin->where('name', 'like', $like);
                });
        });
    }

    /**
     * Read only the day asked for, between the two times when both are given.
     * A from time later than the to time is read as the whole day.
     */
    private function applyWindow(Builder $query, ?string $date, ?string $fromTime, ?string $toTime): void
    {
        if (! $date || ! Carbon::hasFormat($date, 'Y-m-d')) {
            return;
        }

        $fromTime = $fromTime && Carbon::hasFormat($fromTime, 'H:i') ? $fromTime : '00:00';
        $toTime = $toTime && Carbon::hasFormat($toTime, 'H:i') ? $toTime : '23:59';

        if ($fromTime > $toTime) {
            $query->whereDate('created_at', $date);

            return;
        }

        $query->whereBetween('created_at', [
            Carbon::parse($date . ' ' . $fromTime)->startOfMinute(),
            Carbon::parse($date . ' ' . $toTime)->endOfMinute(),
        ]);
    }
}
