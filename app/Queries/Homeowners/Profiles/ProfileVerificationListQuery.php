<?php

namespace App\Queries\Homeowners\Profiles;

use App\Enums\ProfileStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The profiles the office has to work, in the order it works them: what is
 * waiting first, then what was sent back. A verified profile has left the queue.
 */
class ProfileVerificationListQuery
{
    // ===== Queries =====

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(string $search = '', int $perPage = 15)
    {
        return User::query()
            ->select([
                'id', 'resident_id', 'email', 'profile_status',
                'profile_submitted_at', 'profile_verified_at', 'created_at',
            ])
            ->with([
                'resident:id,surname,first_name,middle_name,mobile_no,valid_id_path',
                'resident.household.property',
            ])
            ->whereIn('profile_status', [
                ProfileStatus::SUBMITTED,
                ProfileStatus::REJECTED,
            ])
            ->when(trim($search) !== '', function ($query) use ($search) {
                $term = '%' . trim($search) . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('email', 'like', $term)
                        ->orWhereHas('resident', function ($resident) use ($term) {
                            $resident->where('surname', 'like', $term)
                                ->orWhere('first_name', 'like', $term)
                                ->orWhere('middle_name', 'like', $term);
                        });
                });
            })
            ->orderByRaw("case profile_status when 'submitted' then 0 else 1 end")
            ->orderByDesc('profile_submitted_at')
            ->paginate($perPage);
    }

    /**
     * Every account by where its profile stands, for the page's status boxes.
     *
     * @return array<int, array{label: string, theme: string, icon: string, count: int}>
     */
    public function statusCounts(): array
    {
        $counts = User::query()
            ->selectRaw('profile_status, count(*) as total')
            ->groupBy('profile_status')
            ->pluck('total', 'profile_status');

        $icons = [
            ProfileStatus::VERIFIED   => 'fas fa-fw fa-user-check',
            ProfileStatus::SUBMITTED  => 'fas fa-fw fa-clock',
            ProfileStatus::REJECTED   => 'fas fa-fw fa-user-edit',
            ProfileStatus::INCOMPLETE => 'fas fa-fw fa-user',
        ];

        $rows = [];

        foreach ([
            ProfileStatus::VERIFIED(),
            ProfileStatus::SUBMITTED(),
            ProfileStatus::REJECTED(),
            ProfileStatus::INCOMPLETE(),
        ] as $status) {
            $rows[] = [
                'label' => $status->label(),
                'theme' => $status->badgeTheme(),
                'icon'  => $icons[$status->value] ?? 'fas fa-fw fa-user',
                'count' => (int) ($counts[$status->value] ?? 0),
            ];
        }

        return $rows;
    }
}
